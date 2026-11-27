<?php
mysqli_report(MYSQLI_REPORT_OFF);

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    header("Location: registrar_usuario.html");
    exit;
}

$documento  = is_string($_POST["documento"] ?? null) ? trim($_POST["documento"]) : "";
$nombre     = is_string($_POST["nombre"] ?? null) ? trim($_POST["nombre"]) : "";
$correo     = is_string($_POST["correo"] ?? null) ? trim($_POST["correo"]) : "";
$telefono   = is_string($_POST["telefono"] ?? null) ? trim($_POST["telefono"]) : "";
$direccion  = is_string($_POST["direccion"] ?? null) ? trim($_POST["direccion"]) : "No registrada";
$contrasena = is_string($_POST["contrasena"] ?? null) ? $_POST["contrasena"] : "";
$rol_seleccionado = $_POST["rol_id_rol"] ?? "";

if (!is_string($rol_seleccionado) || $rol_seleccionado !== "4") {
    http_response_code(400);
    exit("El registro público solo permite crear cuentas de Cliente.");
}

$rol_id_rol = 4;

if ($documento === "" || $nombre === "" || $correo === "" || $telefono === "" || $contrasena === "") {
    http_response_code(400);
    exit("Completa todos los campos obligatorios.");
}

require_once "conexion_bd.php";

$stmt_check = $conexion->prepare("SELECT id_usuario FROM usuario WHERE documento = ? OR correo = ?");
if (!$stmt_check) {
    error_log("No se pudo preparar la verificación de usuario: " . $conexion->error);
    http_response_code(500);
    $conexion->close();
    exit("No fue posible validar el registro. Inténtalo más tarde.");
}

if (!$stmt_check->bind_param("ss", $documento, $correo)) {
    error_log("No se pudieron enlazar los datos de verificación: " . $stmt_check->error);
    http_response_code(500);
    $stmt_check->close();
    $conexion->close();
    exit("No fue posible validar el registro. Inténtalo más tarde.");
}

if (!$stmt_check->execute() || !$stmt_check->store_result()) {
    error_log("No se pudo verificar si el usuario ya existe: " . $stmt_check->error);
    http_response_code(500);
    $stmt_check->close();
    $conexion->close();
    exit("No fue posible validar el registro. Inténtalo más tarde.");
}

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    http_response_code(409);
    $conexion->close();
    exit("Ese documento o correo ya está registrado.");
}
$stmt_check->close();

$hash = password_hash($contrasena, PASSWORD_DEFAULT);
if (!$conexion->begin_transaction()) {
    error_log("No se pudo iniciar la transacción de registro: " . $conexion->error);
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

$stmt = $conexion->prepare(
    "INSERT INTO usuario (nombre, documento, telefono, correo, direccion, contrasena, rol_id_rol)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    error_log("No se pudo preparar la creación del usuario: " . $conexion->error);
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

if (!$stmt->bind_param("ssssssi", $nombre, $documento, $telefono, $correo, $direccion, $hash, $rol_id_rol)) {
    error_log("No se pudieron enlazar los datos del usuario: " . $stmt->error);
    $stmt->close();
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

if (!$stmt->execute()) {
    $codigo_error = $stmt->errno;
    $mensaje_error = $stmt->error;
    $stmt->close();
    $conexion->rollback();
    $conexion->close();

    if ($codigo_error === 1062) {
        http_response_code(409);
        exit("Ese documento o correo ya está registrado.");
    }

    error_log("No se pudo guardar el usuario: " . $mensaje_error);
    http_response_code(500);
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

$id_nuevo_usuario = $conexion->insert_id;
$stmt->close();

$stmt_cliente = $conexion->prepare("INSERT INTO cliente (usuario_id_usuario) VALUES (?)");
if (!$stmt_cliente) {
    error_log("No se pudo preparar el perfil de cliente: " . $conexion->error);
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

if (!$stmt_cliente->bind_param("i", $id_nuevo_usuario)) {
    error_log("No se pudieron enlazar los datos del perfil de cliente: " . $stmt_cliente->error);
    $stmt_cliente->close();
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

if (!$stmt_cliente->execute()) {
    error_log("No se pudo guardar el perfil de cliente: " . $stmt_cliente->error);
    $stmt_cliente->close();
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}
$stmt_cliente->close();

if (!$conexion->commit()) {
    error_log("No se pudo confirmar la transacción de registro: " . $conexion->error);
    $conexion->rollback();
    http_response_code(500);
    $conexion->close();
    exit("No fue posible completar el registro. Inténtalo más tarde.");
}

$conexion->close();
header("Location: registro_exitoso.html");
exit;