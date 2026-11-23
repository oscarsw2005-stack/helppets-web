<?php

// Datos de conexión
$servidor = "localhost";
$usuario = "root";
$password = "";
$baseDatos = "veterinaria";

// Crear conexión
$conexion = new mysqli(
    $servidor,
    $usuario,
    $password,
    $baseDatos
);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar caracteres
$conexion->set_charset("utf8mb4");


?>