<?php
$titulo = 'Contratos | Houm Bogotá';
$rutaActiva = 'contratos';
$contratos = $contratos ?? [];
$filtros = $filtros ?? [];
$badges = ['ACTIVO' => 'success', 'FINALIZADO' => 'secondary', 'INCUMPLIDO' => 'danger'];
require_once __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">Gestión de Contratos</h1>
    <a class="btn btn-primary" href="index.php?controller=contrato&action=registrar">+ Nuevo Contrato</a>
</div>

<?php if (!empty($_SESSION['exito'])): ?>
    <div class="alert alert-success rounded-4"><?= htmlspecialchars($_SESSION['exito'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['exito']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="get" action="index.php" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <input type="hidden" name="controller" value="contrato">
    <input type="hidden" name="action" value="listar">
    <div class="row g-2">
        <div class="col-md-6">
            <input class="form-control" name="busqueda" placeholder="Buscar por inmueble, arrendatario o código..." value="<?= htmlspecialchars($filtros['busqueda'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="estado">
                <option value="">Todos los estados</option>
                <?php foreach ($badges as $estado => $badge): ?>
                    <option value="<?= $estado ?>" <?= ($filtros['estado'] ?? '') === $estado ? 'selected' : '' ?>><?= ucfirst(strtolower($estado)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Filtrar</button></div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>ID</th><th>Código</th><th>Inmueble</th><th>Arrendatario</th><th>Vigencia</th><th>Canon</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if (!$contratos): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron contratos.</td></tr>
            <?php endif; ?>
            <?php foreach ($contratos as $c): ?>
                <tr>
                    <td><?= (int) $c['id'] ?></td>
                    <td><code><?= htmlspecialchars($c['codigo_verificacion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= htmlspecialchars($c['inmueble_titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($c['arrendatario_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-nowrap"><?= htmlspecialchars($c['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($c['fecha_fin'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format((float) $c['canon_mensual'], 0, ',', '.') ?></td>
                    <td><span class="badge bg-<?= $badges[$c['estado']] ?? 'secondary' ?>"><?= ucfirst(strtolower($c['estado'])) ?></span></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="index.php?controller=contrato&action=detalle&id=<?= (int) $c['id'] ?>">Detalle</a>
                        <a class="btn btn-sm btn-outline-secondary" href="index.php?controller=contrato&action=pdf&id=<?= (int) $c['id'] ?>"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
