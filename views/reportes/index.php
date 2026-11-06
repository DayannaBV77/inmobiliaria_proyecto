<?php
$titulo = 'Reportes | Houm Bogotá';
$rutaActiva = 'reportes';
$indicadores = $indicadores ?? [];
$cartera = $cartera ?? [];
$colocacion = $colocacion ?? [];
$porTipo = $porTipo ?? [];
$porCiudad = $porCiudad ?? [];
$recaudoMes = $recaudoMes ?? [];
$cards = [
    ['Contratos activos', number_format((int) ($indicadores['contratos_activos'] ?? 0)), 'bi-file-earmark-check'],
    ['Contratos incumplidos', number_format((int) ($indicadores['contratos_incumplidos'] ?? 0)), 'bi-exclamation-triangle'],
    ['Recaudo PAGADO', '$' . number_format((float) ($indicadores['recaudo_pagado'] ?? 0), 0, ',', '.'), 'bi-cash-coin'],
    ['Cartera pendiente', '$' . number_format((float) ($indicadores['cartera_pendiente'] ?? 0), 0, ',', '.'), 'bi-hourglass-split'],
    ['Monto rechazado', '$' . number_format((float) ($indicadores['monto_rechazado'] ?? 0), 0, ',', '.'), 'bi-x-circle'],
    ['Canon promedio (activos)', '$' . number_format((float) ($indicadores['canon_promedio_activos'] ?? 0), 0, ',', '.'), 'bi-calculator'],
];
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="h4 fw-bold mb-4">Reportes e Indicadores</h1>

<section class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-4">
    <?php foreach ($cards as [$etiqueta, $valor, $icono]): ?>
        <div class="col"><div class="card h-100 border-0 shadow-sm rounded-4"><div class="card-body d-flex align-items-center gap-3">
            <span class="d-inline-grid place-items-center rounded-4 bg-primary-subtle text-primary" style="width:3.25rem;height:3.25rem;"><i class="bi <?= $icono ?> fs-4"></i></span>
            <div><div class="fs-4 fw-bold"><?= $valor ?></div><div class="text-muted"><?= $etiqueta ?></div></div>
        </div></div></div>
    <?php endforeach; ?>
</section>

<section class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h2 class="h5 fw-bold mb-3">Cartera Morosa</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Contrato</th><th>Inmueble</th><th>Arrendatario</th><th>Pagado</th><th>Pendiente</th><th>Rechazado</th><th>Saldo moroso</th><th>Días mora</th></tr></thead>
                <tbody>
                <?php if (!$cartera): ?><tr><td colspan="8" class="text-center text-muted py-3">Sin cartera morosa registrada.</td></tr><?php endif; ?>
                <?php foreach ($cartera as $c): ?>
                    <tr>
                        <td>#<?= (int) $c['contrato_id'] ?> <code><?= htmlspecialchars($c['codigo_verificacion'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars($c['inmueble'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['arrendatario'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>$<?= number_format((float) $c['total_pagado'], 0, ',', '.') ?></td>
                        <td>$<?= number_format((float) $c['total_pendiente'], 0, ',', '.') ?></td>
                        <td>$<?= number_format((float) $c['total_rechazado'], 0, ',', '.') ?></td>
                        <td class="fw-bold text-danger">$<?= number_format((float) $c['saldo_moroso'], 0, ',', '.') ?></td>
                        <td><?= $c['dias_mora'] ? (int) $c['dias_mora'] . ' días' : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="row g-4">
    <section class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-3">Tiempos de Colocación (días)</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Contrato</th><th>Inmueble</th><th>Publicación</th><th>Inicio</th><th>Días</th></tr></thead>
                        <tbody>
                        <?php if (!$colocacion): ?><tr><td colspan="5" class="text-center text-muted py-3">Sin datos de colocación.</td></tr><?php endif; ?>
                        <?php foreach ($colocacion as $t): ?>
                            <tr><td>#<?= (int) $t['contrato_id'] ?></td><td><?= htmlspecialchars($t['inmueble'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(substr($t['fecha_publicacion'], 0, 10), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($t['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-bold"><?= (int) $t['dias_colocacion'] ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <section class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2">Promedio por tipo</h2>
                <?php foreach ($porTipo as $t): ?><div class="d-flex justify-content-between py-1 text-capitalize"><span><?= htmlspecialchars($t['tipo'], ENT_QUOTES, 'UTF-8') ?></span><strong><?= $t['promedio_dias'] ?> días</strong></div><?php endforeach; ?>
                <h2 class="h6 fw-bold mb-2 mt-3">Promedio por ciudad</h2>
                <?php foreach ($porCiudad as $t): ?><div class="d-flex justify-content-between py-1"><span><?= htmlspecialchars($t['ciudad'], ENT_QUOTES, 'UTF-8') ?></span><strong><?= $t['promedio_dias'] ?> días</strong></div><?php endforeach; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2">Recaudo por mes (PAGADO)</h2>
                <?php foreach ($recaudoMes as $m): ?><div class="d-flex justify-content-between py-1"><span><?= htmlspecialchars($m['mes'], ENT_QUOTES, 'UTF-8') ?></span><strong>$<?= number_format((float) $m['total'], 0, ',', '.') ?></strong></div><?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
