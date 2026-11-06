<?php
$titulo = 'Dashboard | Houm Bogotá';
require_once __DIR__ . '/../layout/header.php';

$kpis = $kpis ?? [];
$usuario = $usuario ?? ($_SESSION['usuario'] ?? ['nombre' => 'Dayanna']);
$primerNombre = explode(' ', trim($usuario['nombre'] ?? 'Dayanna') ?: 'Dayanna')[0];
$metricas = [
    ['Total Inmuebles', number_format((int) ($kpis['total_inmuebles'] ?? 0)), (int) ($kpis['inmuebles_disponibles'] ?? 0) . ' disponibles', 'bi-buildings', 'bg-primary-subtle text-primary'],
    ['Reservas Activas', number_format((int) ($kpis['reservas_activas'] ?? 0)), (int) ($kpis['citas_programadas'] ?? 0) . ' citas programadas', 'bi-calendar-check', 'bg-warning-subtle text-warning-emphasis'],
    ['Contratos Activos', number_format((int) ($kpis['contratos_activos'] ?? 0)), (int) ($kpis['total_contratos'] ?? 0) . ' totales', 'bi-file-earmark-check', 'bg-success-subtle text-success-emphasis'],
    ['Ingresos del Mes', '$' . number_format((float) ($kpis['ingresos_total'] ?? 0), 0, ',', '.'), 'recaudo PAGADO', 'bi-cash-coin', 'bg-info-subtle text-info-emphasis'],
];
$accesos = [
    ['Agregar Inmueble', 'bi-plus-circle', 'index.php?controller=inmueble&action=registrar'],
    ['Gestionar Reservas', 'bi-calendar-check', 'index.php?controller=inmueble&action=listar&estado=RESERVADO'],
    ['Ver Reportes', 'bi-bar-chart', 'index.php?controller=reporte&action=index'],
];
?>

<section class="mb-4">
    <h1 class="h3 fw-bold mb-1">¡Bienvenida, <?= htmlspecialchars($primerNombre, ENT_QUOTES, 'UTF-8') ?>! Aquí tienes un resumen de tu actividad</h1>
    <p class="text-muted mb-0">Panel de control inmobiliario</p>
</section>

<section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
    <?php foreach ($metricas as [$etiqueta, $valor, $aumento, $icono, $clasesIcono]): ?>
        <div class="col">
            <article class="card h-100 border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="d-inline-grid place-items-center rounded-4 <?= $clasesIcono ?>" style="width:3.25rem;height:3.25rem;"><i class="bi <?= $icono ?> fs-4"></i></span>
                        <span class="badge bg-success-subtle text-success rounded-pill"><?= $aumento ?></span>
                    </div>
                    <div class="fs-3 fw-bold"><?= $valor ?></div>
                    <div class="text-muted"><?= $etiqueta ?></div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</section>

<div class="row g-4">
    <section class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Accesos Rápidos</h2>
                <div class="list-group list-group-flush">
                    <?php foreach ($accesos as [$etiqueta, $icono, $url]): ?>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-0 py-3" href="<?= $url ?>">
                            <span><i class="bi <?= $icono ?> text-primary me-2"></i><?= $etiqueta ?></span>
                            <i class="bi bi-arrow-right text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Actividad Mensual</h2>
                <div id="actividadMensual" class="position-relative border rounded-4 bg-light" style="height:320px;">
                    <span class="position-absolute top-50 start-50 translate-middle text-muted small">La gráfica de Actividad Mensual se cargará aquí</span>
                    <canvas id="graficaActividadMensual" class="position-absolute top-0 start-0 w-100 h-100" aria-label="Gráfica de Actividad Mensual"></canvas>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
