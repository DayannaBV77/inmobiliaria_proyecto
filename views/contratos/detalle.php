<?php
$titulo = 'Detalle de Contrato | Houm Bogotá';
$rutaActiva = 'contratos';
$contrato = $contrato ?? [];
require_once __DIR__ . '/../layout/header.php';
?>

<a class="btn btn-link ps-0 mb-3 text-decoration-none" href="index.php?controller=contrato&action=listar"><i class="bi bi-arrow-left me-1"></i>Volver</a>

<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Contrato #<?= (int) ($contrato['id'] ?? 0) ?></h1>
            <span class="text-muted"><?= htmlspecialchars($contrato['inmueble_titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <span class="badge bg-primary fs-6"><?= htmlspecialchars($contrato['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="alert alert-info rounded-4 d-flex align-items-center">
        <i class="bi bi-shield-check me-2"></i>
        <span>Código de verificación: <strong><code><?= htmlspecialchars($contrato['codigo_verificacion'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></code></strong></span>
        <a class="btn btn-sm btn-outline-primary ms-auto" href="index.php?controller=contrato&action=verificar&codigo=<?= urlencode($contrato['codigo_verificacion'] ?? '') ?>">Verificar</a>
    </div>

    <dl class="row mb-4">
        <dt class="col-sm-3">Arrendatario</dt><dd class="col-sm-9"><?= htmlspecialchars($contrato['arrendatario_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($contrato['arrendatario_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</dd>
        <dt class="col-sm-3">Propietario</dt><dd class="col-sm-9"><?= htmlspecialchars($contrato['propietario_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($contrato['propietario_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</dd>
        <dt class="col-sm-3">Inmueble</dt><dd class="col-sm-9"><?= htmlspecialchars($contrato['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($contrato['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-3">Vigencia</dt><dd class="col-sm-9"><?= htmlspecialchars($contrato['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($contrato['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-3">Canon mensual</dt><dd class="col-sm-9 fw-bold text-primary">$<?= number_format((float) ($contrato['canon_mensual'] ?? 0), 0, ',', '.') ?></dd>
    </dl>

    <a class="btn btn-primary" href="index.php?controller=contrato&action=pdf&id=<?= (int) ($contrato['id'] ?? 0) ?>"><i class="bi bi-file-earmark-pdf me-2"></i>Descargar PDF</a>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
