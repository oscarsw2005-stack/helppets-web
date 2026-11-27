<?php
session_start();
require_once "conexion_bd.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

$documento  = trim($_POST["documento"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

if ($documento === "" || $contrasena === "") {
    die("Por favor ingresa tu documento y contraseña.");
}

// Busca el usuario y el nombre de su rol
$sql = "SELECT u.id_usuario, u.nombre, u.documento, u.correo, u.telefono, u.direccion, u.contrasena, u.rol_id_rol, r.nombre_rol
        FROM usuario u
        INNER JOIN rol r ON u.rol_id_rol = r.id_rol
        WHERE u.documento = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $documento);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conexion->close();
    die("Documento o contraseña incorrectos.");
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

// Valida contraseñas encriptadas (password_hash) y también texto plano (ej. '123456' de prueba)
$pass_bd = (string) $usuario["contrasena"];
$password_valida = password_verify($contrasena, $pass_bd) || hash_equals($pass_bd, $contrasena);

if (!$password_valida) {
    $conexion->close();
    die("Documento o contraseña incorrectos.");
}

// Guarda los datos clave en la sesión
$_SESSION["id_usuario"] = (int) $usuario["id_usuario"];
$_SESSION["nombre"]     = $usuario["nombre"];
$_SESSION["documento"]  = $usuario["documento"];
$_SESSION["rol_id"]     = (int) $usuario["rol_id_rol"];
$_SESSION["rol_nombre"] = $usuario["nombre_rol"];

// Si es Cliente (rol 4), guarda también su idCliente para facilitar otros módulos
if ((int) $usuario["rol_id_rol"] === 4) {
    $stmt_cli = $conexion->prepare("SELECT idCliente FROM cliente WHERE usuario_id_usuario = ? LIMIT 1");
    if ($stmt_cli) {
        $stmt_cli->bind_param("i", $_SESSION["id_usuario"]);
        $stmt_cli->execute();
        $res_cli = $stmt_cli->get_result();
        if ($fila_cli = $res_cli->fetch_assoc()) {
            $_SESSION["id_cliente"] = (int) $fila_cli["idCliente"];
        }
        $stmt_cli->close();
    }
}

$conexion->close();

// Redirección según el rol en la tabla `rol`
switch ((int) $usuario["rol_id_rol"]) {
    case 1: // Administrador
        header("Location: opciones_administrador.html");
        break;
    case 2: // Recepcionista
        header("Location: panel_control.html");
        break;
    case 3: // Veterinario
        header("Location: Panel_veterinaria.html");
        break;
    case 4: // Cliente
    default:
        header("Location: panel_cliente.html");
        break;
}
exit;