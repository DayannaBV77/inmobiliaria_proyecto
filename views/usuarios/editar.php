<?php
$titulo = 'Editar Usuario | Houm Bogotá';
$rutaActiva = 'usuarios';
$editado = $editado ?? [];
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="h4 fw-bold mb-4">Editar Usuario</h1>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="post" action="index.php?controller=usuario&action=editar&id=<?= (int) ($editado['id'] ?? 0) ?>" class="card border-0 shadow-sm rounded-4 p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="nombre">Nombre completo *</label>
            <input class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($editado['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Correo electrónico *</label>
            <input class="form-control" id="email" name="email" type="email" required value="<?= htmlspecialchars($editado['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="telefono">Teléfono *</label>
            <input class="form-control" id="telefono" name="telefono" required value="<?= htmlspecialchars($editado['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rol">Rol *</label>
            <select class="form-select" id="rol" name="rol" required>
                <?php foreach (['administrador', 'houmer', 'propietario', 'arrendatario'] as $rol): ?>
                    <option value="<?= $rol ?>" <?= ($editado['rol'] ?? '') === $rol ? 'selected' : '' ?>><?= ucfirst($rol) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="password">Nueva contraseña (dejar vacío para mantener)</label>
            <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password">
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="index.php?controller=usuario&action=listar">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar cambios</button>
    </div>
</form>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
