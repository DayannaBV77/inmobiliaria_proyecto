<?php
$titulo = 'Usuarios | Houm Bogotá';
$rutaActiva = 'usuarios';
$usuarios = $usuarios ?? [];
$filtros = $filtros ?? [];
$esAdmin = $esAdmin ?? false;
$badges = ['administrador' => 'danger', 'houmer' => 'primary', 'propietario' => 'info', 'arrendatario' => 'secondary'];
require_once __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">Gestión de Usuarios</h1>
    <?php if ($esAdmin): ?>
        <a class="btn btn-primary" href="index.php?controller=usuario&action=registrar">+ Nuevo Usuario</a>
    <?php endif; ?>
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
    <input type="hidden" name="controller" value="usuario">
    <input type="hidden" name="action" value="listar">
    <div class="row g-2">
        <div class="col-md-5">
            <input class="form-control" name="busqueda" placeholder="Buscar por nombre, correo o teléfono..." value="<?= htmlspecialchars($filtros['busqueda'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-4">
            <select class="form-select" name="rol">
                <option value="">Todos los roles</option>
                <?php foreach ($badges as $rol => $badge): ?>
                    <option value="<?= $rol ?>" <?= ($filtros['rol'] ?? '') === $rol ? 'selected' : '' ?>><?= ucfirst($rol) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Filtrar</button></div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Registro</th><?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$usuarios): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron usuarios.</td></tr>
            <?php endif; ?>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge bg-<?= $badges[$u['rol']] ?? 'secondary' ?> text-capitalize"><?= htmlspecialchars($u['rol'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars(substr($u['created_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if ($esAdmin): ?>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="index.php?controller=usuario&action=editar&id=<?= (int) $u['id'] ?>">Editar</a>
                            <form class="d-inline" method="post" action="index.php?controller=usuario&action=eliminar" onsubmit="return confirm('¿Eliminar este usuario?');">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
