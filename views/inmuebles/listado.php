<?php
require_once __DIR__ . '/../../config/imagenes.php';
$titulo = 'Inmuebles | Houm Bogotá';
$rutaActiva = ($_GET['estado'] ?? '') === 'RESERVADO' ? 'reservas' : 'inmuebles';
$inmuebles = $inmuebles ?? [];
$filtros = $filtros ?? [];
$mostrarExito = !empty($_SESSION['exito']);
unset($_SESSION['exito']);
$badges = [
    'DISPONIBLE' => ['success', 'Disponible'],
    'RESERVADO' => ['secondary', 'Reservado'],
    'ARRENDADO' => ['dark', 'Arrendado'],
    'MANTENIMIENTO' => ['danger', 'Mantenimiento'],
];
$zonas = array_values(array_unique(array_filter(array_map(fn($i) => trim($i['ciudad'] ?? ''), $inmuebles))));
if (!in_array('Bogotá', $zonas, true)) {
    $zonas[] = 'Bogotá';
}
if (!empty($filtros['zona']) && !in_array($filtros['zona'], $zonas, true)) {
    $zonas[] = $filtros['zona'];
}
require_once __DIR__ . '/../layout/header.php';
?>

<form method="get" action="index.php" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <input type="hidden" name="controller" value="inmueble">
    <input type="hidden" name="action" value="listar">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input class="form-control" name="busqueda" placeholder="Buscar inmueble..." value="<?= htmlspecialchars($filtros['busqueda'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="estado" aria-label="Estado">
                <option value="">Todos los estados</option>
                <?php foreach ($badges as $valor => $dato): ?>
                    <option value="<?= $valor ?>" <?= ($filtros['estado'] ?? '') === $valor ? 'selected' : '' ?>><?= $dato[1] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="zona" aria-label="Zona">
                <option value="">Todas las zonas</option>
                <?php foreach ($zonas as $zona): ?>
                    <option value="<?= htmlspecialchars($zona, ENT_QUOTES, 'UTF-8') ?>" <?= ($filtros['zona'] ?? '') === $zona ? 'selected' : '' ?>><?= htmlspecialchars($zona, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 text-md-end">
            <a class="btn btn-primary w-100" href="index.php?controller=inmueble&action=registrar">+ Nuevo Inmueble</a>
        </div>
    </div>
</form>

<?php if (!$inmuebles): ?>
    <div class="alert alert-info rounded-4">No se encontraron inmuebles con los filtros aplicados.</div>
<?php else: ?>
    <section class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($inmuebles as $inmueble): ?>
            <?php
            $badge = $badges[$inmueble['estado']] ?? ['secondary', $inmueble['estado']];
            $imagen = htmlspecialchars(imagenInmueble($inmueble), ENT_QUOTES, 'UTF-8');
            $precio = !empty($inmueble['precio_canon']) ? '$' . number_format((float) $inmueble['precio_canon'], 0, ',', '.') . '/mes' : '$' . number_format((float) ($inmueble['precio_venta'] ?? 0), 0, ',', '.');
            ?>
            <div class="col">
                <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <img class="card-img-top" style="height:220px;object-fit:cover;" src="<?= $imagen ?>" alt="<?= htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h2 class="h5 mb-0"><?= htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="badge bg-<?= $badge[0] ?>"><?= $badge[1] ?></span>
                        </div>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($inmueble['direccion'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($inmueble['ciudad'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="small text-muted mb-3">
                            <i class="bi bi-moon-stars me-1"></i><?= (int) $inmueble['habitaciones'] ?>
                            <i class="bi bi-droplet ms-2 me-1"></i><?= (int) $inmueble['banos'] ?>
                            <i class="bi bi-rulers ms-2 me-1"></i><?= number_format((float) $inmueble['area_m2'], 0) ?> m²
                        </p>
                        <div class="fw-bold text-primary fs-5 mb-3"><?= $precio ?></div>
                        <a class="btn btn-primary w-100 mt-auto" href="index.php?controller=inmueble&action=detalle&id=<?= (int) $inmueble['id'] ?>">Ver detalle <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center p-4">
                <span class="d-inline-grid place-items-center rounded-circle bg-success text-white mb-3" style="width:4rem;height:4rem;background-color:#10B981 !important;"><i class="bi bi-check-lg fs-3"></i></span>
                <h2 class="h5 fw-bold" id="modalExitoTitulo">¡Operación exitosa!</h2>
                <p class="text-muted">El inmueble se ha registrado correctamente.</p>
                <button class="btn btn-primary px-4" type="button" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mostrarExito = <?= $mostrarExito ? 'true' : 'false' ?>;
        if (mostrarExito) {
            new bootstrap.Modal(document.getElementById('modalExito')).show();
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
