<?php
$titulo = 'Nuevo Usuario | Houm Bogotá';
$rutaActiva = 'usuarios';
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="h4 fw-bold mb-4">Registrar Usuario</h1>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="post" action="index.php?controller=usuario&action=registrar" class="card border-0 shadow-sm rounded-4 p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="nombre">Nombre completo *</label>
            <input class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Correo electrónico *</label>
            <input class="form-control" id="email" name="email" type="email" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="telefono">Teléfono *</label>
            <input class="form-control" id="telefono" name="telefono" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="rol">Rol *</label>
            <select class="form-select" id="rol" name="rol" required>
                <option value="administrador">Administrador</option>
                <option value="houmer">Empleado / Houmer</option>
                <option value="propietario">Propietario</option>
                <option value="arrendatario" selected>Arrendatario</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="password">Contraseña * (mín. 8 caracteres)</label>
            <input class="form-control" id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="index.php?controller=usuario&action=listar">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</form>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
