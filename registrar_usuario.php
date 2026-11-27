<?php
require_once "conexion_bd.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $documento  = trim($_POST["documento"] ?? "");
    $nombre     = trim($_POST["nombre"] ?? "");
    $correo     = trim($_POST["correo"] ?? "");
    $telefono   = trim($_POST["telefono"] ?? "");
    $direccion  = trim($_POST["direccion"] ?? "No registrada");
    $contrasena = $_POST["contrasena"] ?? "";
    $rol_id_rol = 4; // Cliente

    if ($documento === "" || $nombre === "" || $correo === "" || $telefono === "" || $contrasena === "") {
        die("Completa todos los campos obligatorios.");
    }

    // Comprobar si ya existe documento o correo
    $stmt_check = $conexion->prepare("SELECT id_usuario FROM usuario WHERE documento = ? OR correo = ?");
    $stmt_check->bind_param("ss", $documento, $correo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        die("Ese documento o correo ya está registrado.");
    }
    $stmt_check->close();

    // Guardar contraseña encriptada (COLUMNA VARCHAR(255))
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $conexion->prepare(
        "INSERT INTO usuario (nombre, documento, telefono, correo, direccion, contrasena, rol_id_rol)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssssi", $nombre, $documento, $telefono, $correo, $direccion, $hash, $rol_id_rol);

    if ($stmt->execute()) {
        $id_nuevo_usuario = $conexion->insert_id;

        if ($rol_id_rol === 4) {
            $stmt_cli = $conexion->prepare("INSERT INTO cliente (usuario_id_usuario) VALUES (?)");
            $stmt_cli->bind_param("i", $id_nuevo_usuario);
            $stmt_cli->execute();
            $stmt_cli->close();
        }

        $stmt->close();
        $conexion->close();

        header("Location: registro_exitoso.html");
        exit;
    } else {
        echo "Error al guardar el usuario: " . $stmt->error;
        $stmt->close();
    }
}

$conexion->close();
