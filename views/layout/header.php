<?php
$titulo = $titulo ?? 'Dashboard | Houm Bogotá';
$usuario = $usuario ?? ($_SESSION['usuario'] ?? ['nombre' => 'Dayanna', 'rol' => '']);
$nombreUsuario = trim($usuario['nombre'] ?? 'Dayanna') ?: 'Dayanna';
$inicialUsuario = strtoupper(mb_substr($nombreUsuario, 0, 1));
$rutaActiva = $rutaActiva ?? ((($_GET['controller'] ?? '') === 'inmueble' && ($_GET['action'] ?? '') === 'dashboard') ? 'dashboard' : ((($_GET['controller'] ?? '') === 'inmueble') ? 'inmuebles' : 'dashboard'));
$claveActiva = $rutaActiva === 'dashboard' ? 'inicio' : (($rutaActiva === 'inmuebles' && ($_GET['estado'] ?? '') === 'RESERVADO') ? 'reservas' : $rutaActiva);
$enlaces = [
    ['inicio', 'Inicio', 'bi-house-door', 'index.php?controller=inmueble&action=dashboard'],
    ['inmuebles', 'Inmuebles', 'bi-building', 'index.php?controller=inmueble&action=listar'],
    ['reservas', 'Reservas', 'bi-calendar-check', 'index.php?controller=inmueble&action=listar&estado=RESERVADO'],
    ['contratos', 'Contratos', 'bi-file-earmark-text', 'index.php?controller=contrato&action=listar'],
    ['reportes', 'Reportes', 'bi-bar-chart', 'index.php?controller=reporte&action=index'],
    ['usuarios', 'Usuarios', 'bi-people', 'index.php?controller=usuario&action=listar'],
    ['configuracion', 'Configuración', 'bi-gear', 'index.php?controller=configuracion&action=index'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="public/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <aside id="sidebar" class="sidebar offcanvas-lg offcanvas-start p-3 vh-100 position-sticky top-0 d-flex flex-column" aria-label="Menú principal">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a class="logo" href="index.php?controller=inmueble&action=dashboard"><i class="bi bi-building me-2"></i>Houm <span>Bogotá</span></a>
            <button class="btn btn-sm btn-outline-light d-lg-none" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
        </div>
        <nav class="nav flex-column gap-1 flex-grow-0">
            <?php foreach ($enlaces as [$clave, $etiqueta, $icono, $url]): ?>
                <a class="nav-link <?= $claveActiva === $clave ? 'active' : '' ?>" href="<?= $url ?>"><i class="bi <?= $icono ?> me-2"></i><?= $etiqueta ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="mt-auto pt-4 border-top border-white-50">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="user-avatar"><?= htmlspecialchars($inicialUsuario, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($usuario['rol'])): ?>
                        <small class="text-white-50 text-capitalize"><?= htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <a class="btn btn-light w-100" href="index.php?controller=auth&action=logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
        </div>
    </aside>

    <div class="flex-grow-1 d-flex flex-column min-vw-0">
        <header class="topbar d-flex align-items-center gap-3 px-3 px-lg-4 py-3 sticky-top">
            <button class="btn btn-outline-primary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
            <span class="fw-bold d-lg-none">Houm <span class="text-primary">Bogotá</span></span>
            <span class="d-none d-lg-inline fw-semibold text-muted"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></span>
        </header>
        <main class="p-3 p-lg-4 flex-grow-1">
