<?php
session_start();

require_once __DIR__ . '/conexion_bd.php';

$documento = trim($_POST['documento'] ?? '');
$contrasena = $_POST['password'] ?? '';
$mensaje = '';
$codigoEstado = 400;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $documento === '' || $contrasena === '') {
    $mensaje = 'Ingresa tu documento y contraseña para iniciar sesión.';
} else {
    $consulta = $conexion->prepare(
        'SELECT u.id_usuario, u.contrasena, r.nombre_rol,
                (c.idCliente IS NOT NULL) AS es_cliente
         FROM usuario u
         LEFT JOIN rol r ON r.id_rol = u.rol_id_rol
         LEFT JOIN cliente c ON c.usuario_id_usuario = u.id_usuario
         WHERE TRIM(u.documento) = ?
         LIMIT 1'
    );
    $consulta->bind_param('s', $documento);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $usuario = $resultado->fetch_assoc();
    $consulta->close();

    if (!$usuario) {
        $codigoEstado = 404;
        $mensaje = 'No encontramos ese documento en la base veterinaria. Verifica que XAMPP use la base correcta; si no tienes cuenta, regístrate.';
    } elseif (
        !password_verify($contrasena, $usuario['contrasena'])
        && !hash_equals((string) $usuario['contrasena'], $contrasena)
    ) {
        $codigoEstado = 401;
        $mensaje = 'La contraseña no es correcta. Verifícala e inténtalo de nuevo.';
    } else {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
        $nombreRol = trim((string) $usuario['nombre_rol']);
        $esCliente = (bool) $usuario['es_cliente']
            || strcasecmp($nombreRol, 'Cliente') === 0;
        $_SESSION['rol'] = $esCliente ? 'Cliente' : $nombreRol;

        if ($esCliente) {
            header('Location: panel_cliente.php');
            exit;
        }

        if (strcasecmp($nombreRol, 'Administrador') === 0) {
            header('Location: panel_administrador.html');
            exit;
        }

        $codigoEstado = 403;
        $mensaje = 'Tu cuenta no tiene un panel disponible en este momento.';
    }
}

http_response_code($codigoEstado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Problema al iniciar sesión - Help Pets</title>
    <link rel="stylesheet" href="estilos/inicio_sesion.css">
</head>
<body>
    <header class="header-banner">
        <div class="header-content">
            <h1>Inicio de Sesión</h1>
        </div>
    </header>
    <main class="main-container">
        <section class="login-card-container">
            <div class="login-card">
                <h2 class="card-title">No pudimos iniciar sesión</h2>
                <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="links-group">
                    <a href="index.html">Volver a intentar</a>
                    <a href="registro.html">Registrarme</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
