<?php
session_start();
require_once "conexion_bd.php";

// 1. Verificar que el usuario tenga sesión iniciada
if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: actualizacion_datos.html");
    exit;
}

$id_usuario   = (int) $_SESSION["id_usuario"];
$nombre       = trim($_POST["nombre"] ?? "");
$telefono     = trim($_POST["telefono"] ?? "");
$correo       = trim($_POST["email"] ?? "");
$direccion    = trim($_POST["direccion"] ?? "");
$password_old = $_POST["password_old"] ?? "";
$password_new = $_POST["password_new"] ?? "";

// 2. Validar campos obligatorios básicos
if ($nombre === "" || $telefono === "" || $correo === "" || $direccion === "") {
    die("Completa nombre, teléfono, correo y dirección.");
}

// 3. Verificar que el correo no pertenezca a otro usuario distinto
$stmt_correo = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = ? AND id_usuario != ?");
$stmt_correo->bind_param("si", $correo, $id_usuario);
$stmt_correo->execute();
$stmt_correo->store_result();

if ($stmt_correo->num_rows > 0) {
    $stmt_correo->close();
    die("Ese correo electrónico ya está en uso por otra cuenta.");
}
$stmt_correo->close();

// 4. Si quiere cambiar contraseña, validar la contraseña anterior
$actualizar_pass = false;
$nuevo_hash = null;

if ($password_new !== "") {
    if ($password_old === "") {
        die("Debes ingresar tu contraseña anterior para establecer una nueva.");
    }

    $stmt_pass = $conexion->prepare("SELECT contrasena FROM usuario WHERE id_usuario = ?");
    $stmt_pass->bind_param("i", $id_usuario);
    $stmt_pass->execute();
    $stmt_pass->bind_result($pass_actual_bd);
    $stmt_pass->fetch();
    $stmt_pass->close();

    // Soporta contraseñas hasheadas y texto plano (por ejemplo '123456' de datos de prueba)
    $es_valida = password_verify($password_old, (string) $pass_actual_bd) || ($password_old === (string) $pass_actual_bd);

    if (!$es_valida) {
        die("La contraseña anterior es incorrecta.");
    }

    $nuevo_hash = password_hash($password_new, PASSWORD_DEFAULT);
    $actualizar_pass = true;
}

// 5. Actualizar en la base de datos
if ($actualizar_pass) {
    $sql = "UPDATE usuario 
            SET nombre = ?, telefono = ?, correo = ?, direccion = ?, contrasena = ? 
            WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $telefono, $correo, $direccion, $nuevo_hash, $id_usuario);
} else {
    $sql = "UPDATE usuario 
            SET nombre = ?, telefono = ?, correo = ?, direccion = ? 
            WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $telefono, $correo, $direccion, $id_usuario);
}

if ($stmt->execute()) {
    $_SESSION["nombre"] = $nombre;
    $stmt->close();
    $conexion->close();

    header("Location: mensaje_exitoso_actualizacion_datos.html");
    exit;
} else {
    $error = $stmt->error;
    $stmt->close();
    $conexion->close();
    die("Error al actualizar los datos: " . $error);
}