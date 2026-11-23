<?php
require "conexion_bd.php";

// Temporal: con login, este número saldrá del usuario que inició sesión
$idCliente = 1;

// 1. Filtros que llegan por la URL
$filtroMascota = isset($_GET['mascota']) ? (int) $_GET['mascota'] : 0;
$filtroVet     = isset($_GET['veterinario']) ? (int) $_GET['veterinario'] : 0;
$orden = (isset($_GET['fecha']) && $_GET['fecha'] === 'antiguas') ? 'ASC' : 'DESC';

// 2. Opciones del filtro Mascota (solo las del cliente)
$stmt = $conexion->prepare("SELECT idmascota, nombre FROM mascota WHERE Cliente_idCliente = ? ORDER BY nombre");
$stmt->bind_param("i", $idCliente);
$stmt->execute();
$mascotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 3. Opciones del filtro Veterinario
$veterinarios = $conexion->query(
    "SELECT v.idveterinario, u.nombre
     FROM veterinario v
     JOIN usuario u ON v.usuario_id_usuario = u.id_usuario
     ORDER BY u.nombre"
)->fetch_all(MYSQLI_ASSOC);

// 4. Consulta del historial (la de tu script, con la fecha de la CITA)
$sql = "SELECT 
            c.fecha_hora AS fecha_cita,
            m.nombre AS nombre_mascota,
            m.peso,
            raz.nombre_raza,
            u_vet.nombre AS medico_tratante,
            h.receta_cuidados,
            h.vacunas_desparasitantes
        FROM Historial_Medico h
        JOIN cita c ON h.cita_idcita = c.idcita
        JOIN mascota m ON c.mascota_idmascota = m.idmascota
        JOIN raza raz ON m.raza_idraza = raz.idraza
        JOIN veterinario v ON h.veterinario_idveterinario = v.idveterinario
        JOIN usuario u_vet ON v.usuario_id_usuario = u_vet.id_usuario
        WHERE m.Cliente_idCliente = ?";

$tipos  = "i";
$params = [$idCliente];

if ($filtroMascota > 0) {
    $sql .= " AND m.idmascota = ?";
    $tipos .= "i";
    $params[] = $filtroMascota;
}
if ($filtroVet > 0) {
    $sql .= " AND h.veterinario_idveterinario = ?";
    $tipos .= "i";
    $params[] = $filtroVet;
}

$sql .= " ORDER BY c.fecha_hora $orden";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpPets - Historial Clínico</title>
    <link rel="stylesheet" href="estilos/historial_clinico.css">
</head>
<body class="bg-pattern">

    <!-- Contenedor principal estilo ventana de navegador -->
    <div class="web-page-container">

        <!-- Barra de Dirección de Navegador simulada -->
        <div class="browser-address-bar">
            <span class="browser-dots"></span>
            <span class="browser-url">https://historialcitas.com</span>
        </div>

        <!-- Cabecera superior con el Botón "Volver al panel" -->
        <div class="top-action-bar">
            <a href="panel_cliente.php" class="btn-volver-panel">Volver al panel</a>
        </div>

        <!-- Título principal -->
        <header class="title-bar">
            <h1 class="main-title">HISTORIAL CLINICO</h1>
        </header>

        <!-- Contenido principal con la tabla de historial -->
        <main class="main-content">
            <div class="table-container">

                <form method="GET" action="historial_clinico.php">
                <table class="clinical-table">
                    <thead>
                        <tr>
                            <th>
                                MASCOTA
                                <select name="mascota" class="filter-select" aria-label="Filtrar por Mascota" onchange="this.form.submit()">
                                    <option value="">▼ Todas</option>
                                    <?php foreach ($mascotas as $m) { ?>
                                        <option value="<?= $m['idmascota'] ?>" <?= $m['idmascota'] == $filtroMascota ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['nombre']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </th>
                            <th>
                                FECHA
                                <select name="fecha" class="filter-select" aria-label="Filtrar por Fecha" onchange="this.form.submit()">
                                    <option value="recientes" <?= $orden === 'DESC' ? 'selected' : '' ?>>Más recientes</option>
                                    <option value="antiguas"  <?= $orden === 'ASC'  ? 'selected' : '' ?>>Más antiguas</option>
                                </select>
                            </th>
                            <th>
                                receta y cuidados
                            </th>
                            <th>
                                Vacunas y desparasitantes
                            </th>
                            <th>
                                Veterinario
                                <select name="veterinario" class="filter-select" aria-label="Filtrar por Veterinario" onchange="this.form.submit()">
                                    <option value="">▼ Todos</option>
                                    <?php foreach ($veterinarios as $v) { ?>
                                        <option value="<?= $v['idveterinario'] ?>" <?= $v['idveterinario'] == $filtroVet ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['nombre']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($historial) === 0) { ?>
                            <tr>
                                <td colspan="5">No hay registros en el historial clínico con esos filtros.</td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($historial as $h) { ?>
                            <tr>
                                <td class="data-sample">
                                    <?= htmlspecialchars($h['nombre_mascota']) ?>,
                                    <?= htmlspecialchars($h['nombre_raza']) ?>,
                                    <?= $h['peso'] !== null ? $h['peso'] . ' kg' : 'peso sin registrar' ?>
                                </td>
                                <td class="data-sample"><?= date("d/m/Y H:i", strtotime($h['fecha_cita'])) ?></td>
                                <td><?= htmlspecialchars($h['receta_cuidados']) ?></td>
                                <td><?= htmlspecialchars($h['vacunas_desparasitantes'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($h['medico_tratante']) ?></td>
                            </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
                </form>

            </div>
        </main>

    </div>

</body>
</html>