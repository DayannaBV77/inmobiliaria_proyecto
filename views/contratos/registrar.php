<?php
$titulo = 'Nuevo Contrato | Houm Bogotá';
$rutaActiva = 'contratos';
$inmuebles = $inmuebles ?? [];
$arrendatarios = $arrendatarios ?? [];
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="h4 fw-bold mb-4">Generar Contrato de Arrendamiento</h1>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="post" action="index.php?controller=contrato&action=registrar" class="card border-0 shadow-sm rounded-4 p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="inmueble_id">Inmueble disponible *</label>
            <select class="form-select" id="inmueble_id" name="inmueble_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($inmuebles as $i): ?>
                    <option value="<?= (int) $i['id'] ?>"><?= htmlspecialchars($i['titulo'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($i['ciudad'], ENT_QUOTES, 'UTF-8') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="arrendatario_id">Arrendatario *</label>
            <select class="form-select" id="arrendatario_id" name="arrendatario_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($arrendatarios as $a): ?>
                    <option value="<?= (int) $a['id'] ?>"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($a['email'], ENT_QUOTES, 'UTF-8') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="fecha_inicio">Fecha de inicio *</label>
            <input class="form-control" id="fecha_inicio" name="fecha_inicio" type="date" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="fecha_fin">Fecha de fin *</label>
            <input class="form-control" id="fecha_fin" name="fecha_fin" type="date" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="canon_mensual">Canon mensual (COP) *</label>
            <input class="form-control" id="canon_mensual" name="canon_mensual" type="number" min="1" step="0.01" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="estado">Estado</label>
            <select class="form-select" id="estado" name="estado">
                <option value="ACTIVO">Activo</option>
                <option value="FINALIZADO">Finalizado</option>
                <option value="INCUMPLIDO">Incumplido</option>
            </select>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="index.php?controller=contrato&action=listar">Cancelar</a>
        <button class="btn btn-primary" type="submit">Generar contrato</button>
    </div>
</form>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
