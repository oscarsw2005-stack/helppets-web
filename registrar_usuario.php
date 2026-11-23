<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . '/conexion_bd.php';

function mostrarResultadoRegistro(string $mensaje, bool $exitoso = false, int $codigoEstado = 400): never
{
    http_response_code($codigoEstado);
    $titulo = $exitoso ? 'Registro completado' : 'No pudimos completar el registro';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?> - Help Pets</title>
        <link rel="stylesheet" href="estilos/registro.css">
    </head>
    <body class="bg-pattern">
        <div class="web-page-container">
            <header class="top-bar">
                <div class="options-title"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></div>
            </header>
            <main class="main-content">
                <section class="form-card-container">
                    <h2 class="form-heading"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></h2>
                    <a class="btn-submit" href="<?= $exitoso ? 'index.html' : 'registro.html' ?>">
                        <?= $exitoso ? 'Iniciar sesión' : 'Volver al registro' ?>
                    </a>
                </section>
            </main>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.html');
    exit;
}

$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$documento = trim($_POST['documento'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

if (
    $nombres === ''
    || $apellidos === ''
    || !filter_var($correo, FILTER_VALIDATE_EMAIL)
    || $documento === ''
    || $telefono === ''
    || $direccion === ''
    || strlen($contrasena) < 8
) {
    mostrarResultadoRegistro('Completa todos los campos correctamente. La contraseña debe tener al menos 8 caracteres.');
}

if (!hash_equals($contrasena, $confirmarContrasena)) {
    mostrarResultadoRegistro('Las contraseñas no coinciden. Verifícalas e inténtalo de nuevo.');
}

if (
    mb_strlen($nombres . ' ' . $apellidos, 'UTF-8') > 100
    || mb_strlen($correo, 'UTF-8') > 100
    || mb_strlen($documento, 'UTF-8') > 20
    || mb_strlen($telefono, 'UTF-8') > 20
    || mb_strlen($direccion, 'UTF-8') > 100
) {
    mostrarResultadoRegistro('Uno o más campos superan la longitud permitida.');
}

$consultaRol = $conexion->prepare("SELECT id_rol FROM rol WHERE nombre_rol = 'Cliente' LIMIT 1");
$consultaRol->execute();
$rol = $consultaRol->get_result()->fetch_assoc();
$consultaRol->close();

if (!$rol) {
    error_log('No existe el rol Cliente en la tabla rol.');
    mostrarResultadoRegistro('El registro de clientes no está disponible en este momento.', false, 500);
}

$conexion->begin_transaction();

try {
    $nombre = $nombres . ' ' . $apellidos;
    $hashContrasena = password_hash($contrasena, PASSWORD_DEFAULT);
    $rolId = (int) $rol['id_rol'];

    $insertarUsuario = $conexion->prepare(
        'INSERT INTO usuario (nombre, documento, telefono, correo, direccion, contrasena, rol_id_rol)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insertarUsuario->bind_param(
        'ssssssi',
        $nombre,
        $documento,
        $telefono,
        $correo,
        $direccion,
        $hashContrasena,
        $rolId
    );
    $insertarUsuario->execute();
    $idUsuario = $conexion->insert_id;
    $insertarUsuario->close();

    $insertarCliente = $conexion->prepare('INSERT INTO cliente (usuario_id_usuario) VALUES (?)');
    $insertarCliente->bind_param('i', $idUsuario);
    $insertarCliente->execute();
    $insertarCliente->close();

    $conexion->commit();
} catch (mysqli_sql_exception $error) {
    $conexion->rollback();

    if ($error->getCode() === 1062) {
        mostrarResultadoRegistro('Ya existe una cuenta con ese documento o correo. Inicia sesión o verifica tus datos.', false, 409);
    }

    error_log('Error al registrar un cliente: ' . $error->getMessage());
    mostrarResultadoRegistro('Ocurrió un error al guardar tu registro. Inténtalo más tarde.', false, 500);
}

mostrarResultadoRegistro('Tu cuenta de cliente ya está lista. Ahora puedes iniciar sesión.', true, 201);
