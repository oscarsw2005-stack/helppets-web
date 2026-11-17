<?php
require "conexion_bd.php";

// 1. Recibir datos del formulario
$nombre      = $_POST['nombre'];
$raza        = (int) $_POST['raza'];
$sexo        = $_POST['sexo'];
$cliente     = (int) $_POST['cliente'];
$peso        = (float) $_POST['peso'];
$descripcion = $_POST['descripcion'];
$datosExtra  = trim($_POST['datos_extra']);

// 2. Si datos extra viene vacío, guardamos NULL en la base de datos
if ($datosExtra === "") {
    $datosExtra = null;
}

// 3. INSERT: cada dato va en su propia columna
$sql = "INSERT INTO mascota (nombre, sexo, observaciones, peso, datos_extra, raza_idraza, Cliente_idCliente)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssdsii", $nombre, $sexo, $descripcion, $peso, $datosExtra, $raza, $cliente);

// 4. Ejecutar
if ($stmt->execute()) {
    header("Location: registro_mascota_exitoso.html");
    exit;
} else {
    echo "Error al registrar la mascota: " . $stmt->error;
}

$stmt->close();
$conexion->close();