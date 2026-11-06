<?php
require_once __DIR__ . '/../../config/imagenes.php';
$inmueble = $inmueble ?? [];
$usuario = $usuario ?? null;
$tituloInmueble = $inmueble['titulo'] ?? 'Apartamento Chapinero';
$miniaturas = !empty($inmueble['imagen'])
    ? ['public/uploads/' . $inmueble['imagen'], ...imagenesPorTipo((string) ($inmueble['tipo'] ?? 'apartamento'))]
    : imagenesPorTipo((string) ($inmueble['tipo'] ?? 'apartamento'));
$miniaturas = array_slice(array_map(fn($u) => htmlspecialchars($u, ENT_QUOTES, 'UTF-8'), $miniaturas), 0, 3);
$imagenPrincipal = $miniaturas[0];
$precio = '$' . number_format((float) ($inmueble['precio_canon'] ?? 0), 0, ',', '.') . '/mes';
$telefono = preg_replace('/\D+/', '', $inmueble['propietario_telefono'] ?? '') ?: '6015550101';
$whatsapp = 'https://wa.me/57' . $telefono;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloInmueble, ENT_QUOTES, 'UTF-8') ?> | Houm Bogotá</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="public/css/publico.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-public sticky-top shadow-sm">
    <div class="container">
        <a class="logo-public" href="index.php"><i class="bi bi-building me-2"></i>Houm <span>Bogotá</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPublico" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
        <div class="collapse navbar-collapse" id="menuPublico">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#catalogo">Inmuebles</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#nosotros">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <?php if ($usuario): ?>
                        <a class="btn btn-primary" href="index.php?controller=inmueble&action=dashboard">Mi panel</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary" href="index.php?url=login">Iniciar sesión</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <a class="btn btn-link ps-0 text-decoration-none mb-3" href="index.php#catalogo"><i class="bi bi-arrow-left me-1"></i>Volver al catálogo</a>
    <div class="row g-4">
        <div class="col-lg-6">
            <img id="imagenPrincipal" class="galeria-principal rounded-4 shadow-sm" src="<?= $imagenPrincipal ?>" alt="<?= htmlspecialchars($tituloInmueble, ENT_QUOTES, 'UTF-8') ?>">
            <div class="row row-cols-3 g-2 mt-1">
                <?php foreach ($miniaturas as $miniatura): ?>
                    <div class="col"><img class="miniatura img-thumbnail" src="<?= $miniatura ?>" data-full="<?= $miniatura ?>" alt="Miniatura"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <span class="badge bg-success mb-3">Disponible</span>
            <h1 class="h3 fw-bold"><?= htmlspecialchars($tituloInmueble, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="fs-3 fw-bold text-primary mb-2"><?= $precio ?></div>
            <p class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(($inmueble['direccion'] ?? '') . ', ' . ($inmueble['ciudad'] ?? 'Bogotá'), ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between px-0"><span><i class="bi bi-moon-stars text-primary me-2"></i>Habitaciones</span><strong><?= (int) ($inmueble['habitaciones'] ?? 0) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span><i class="bi bi-droplet text-primary me-2"></i>Baños</span><strong><?= (int) ($inmueble['banos'] ?? 0) ?></strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span><i class="bi bi-rulers text-primary me-2"></i>Área</span><strong><?= number_format((float) ($inmueble['area_m2'] ?? 0), 0) ?> m²</strong></li>
                <li class="list-group-item d-flex justify-content-between px-0"><span><i class="bi bi-layers text-primary me-2"></i>Tipo · Estrato</span><strong class="text-capitalize"><?= htmlspecialchars($inmueble['tipo'] ?? '', ENT_QUOTES, 'UTF-8') ?> · <?= (int) ($inmueble['estrato'] ?? 0) ?></strong></li>
            </ul>
            <h2 class="h6 fw-bold">Descripción</h2>
            <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($inmueble['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="d-grid gap-2">
                <a class="btn btn-primary py-2" href="index.php?url=login"><i class="bi bi-calendar-plus me-2"></i>Agendar visita</a>
                <a class="btn btn-outline-primary py-2" href="<?= $whatsapp ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>Contactar asesor</a>
            </div>
            <small class="text-muted d-block mt-2">Para agendar la visita necesitas iniciar sesión.</small>
        </div>
    </div>
</main>

<footer id="contacto" class="footer-public py-4 mt-4">
    <div class="container small d-flex flex-wrap justify-content-between gap-2">
        <span>© <?= date('Y') ?> Houm Bogotá · Sistema Inmobiliario</span>
        <span><i class="bi bi-telephone me-1"></i>+57 601 555 0101 · <i class="bi bi-envelope me-1"></i>contacto@houmbogota.co</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/publico.js"></script>
</body>
</html>
