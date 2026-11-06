<?php
require_once __DIR__ . '/../../config/imagenes.php';
$titulo = 'Detalle del Inmueble | Houm Bogotá';
$rutaActiva = 'inmuebles';
$inmueble = $inmueble ?? [];
$badges = [
    'DISPONIBLE' => ['success', 'Disponible'],
    'EN_PROCESO_RESERVA' => ['warning', 'En proceso'],
    'RESERVADO' => ['primary', 'Reservado'],
    'ARRENDADO' => ['danger', 'Arrendado'],
    'MANTENIMIENTO' => ['secondary', 'Mantenimiento'],
];
$badge = $badges[$inmueble['estado']] ?? ['success', 'Disponible'];
$tituloInmueble = $inmueble['titulo'] ?? 'Apartamento Chapinero';
$miniaturas = !empty($inmueble['imagen'])
    ? ['public/uploads/' . $inmueble['imagen'], ...imagenesPorTipo((string) ($inmueble['tipo'] ?? 'apartamento'))]
    : imagenesPorTipo((string) ($inmueble['tipo'] ?? 'apartamento'));
$miniaturas = array_slice(array_map(fn($u) => htmlspecialchars($u, ENT_QUOTES, 'UTF-8'), $miniaturas), 0, 3);
$imagenPrincipal = $miniaturas[0];
$precio = !empty($inmueble['precio_canon']) ? '$' . number_format((float) $inmueble['precio_canon'], 0, ',', '.') . '/mes' : '$' . number_format((float) ($inmueble['precio_venta'] ?? 0), 0, ',', '.') . '/mes';
$ubicacion = trim(($inmueble['direccion'] ?? '') . ', ' . ($inmueble['ciudad'] ?? 'Bogotá'), ', ');
require_once __DIR__ . '/../layout/header.php';
?>

<a class="btn btn-link ps-0 mb-3 text-decoration-none" href="index.php?controller=inmueble&action=listar"><i class="bi bi-arrow-left me-1"></i>Volver</a>

<div class="row g-4">
    <div class="col-lg-6">
        <img id="imagenPrincipal" class="img-fluid rounded-4 shadow-sm w-100" style="height:420px;object-fit:cover;" src="<?= $imagenPrincipal ?>" alt="<?= htmlspecialchars($tituloInmueble, ENT_QUOTES, 'UTF-8') ?>">
        <div class="row row-cols-3 g-2 mt-1">
            <?php foreach ($miniaturas as $miniatura): ?>
                <div class="col">
                    <img class="img-thumbnail w-100" style="height:90px;object-fit:cover;cursor:pointer;" src="<?= $miniatura ?>" alt="Miniatura" onclick="document.getElementById('imagenPrincipal').src = this.src">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <span class="badge bg-<?= $badge[0] ?> mb-3"><?= $badge[1] ?></span>
        <h1 class="h3 fw-bold"><?= htmlspecialchars($tituloInmueble, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="fs-4 fw-bold text-primary mb-3"><?= $precio ?></div>
        <p class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8') ?></p>

        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><i class="bi bi-moon-stars text-primary me-2"></i>Habitaciones</span><strong><?= (int) ($inmueble['habitaciones'] ?? 0) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><i class="bi bi-droplet text-primary me-2"></i>Baños</span><strong><?= (int) ($inmueble['banos'] ?? 0) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><i class="bi bi-rulers text-primary me-2"></i>m²</span><strong><?= number_format((float) ($inmueble['area_m2'] ?? 0), 0) ?> m²</strong>
            </li>
        </ul>

        <h2 class="h6 fw-bold">Descripción</h2>
        <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($inmueble['descripcion'] ?? 'Apartamento moderno y bien ubicado, ideal para vivir o invertir.', ENT_QUOTES, 'UTF-8')) ?></p>

        <a class="btn btn-primary w-100 py-2" href="index.php?controller=cita&action=crear&inmueble_id=<?= (int) ($inmueble['id'] ?? 0) ?>"><i class="bi bi-calendar-plus me-2"></i>Agendar visita</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
