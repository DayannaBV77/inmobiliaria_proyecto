<?php
$error = $error ?? ($_SESSION['error'] ?? null);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Houm Bogotá</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="public/css/custom.css" rel="stylesheet">
</head>
<body>
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
        <div class="card shadow-sm rounded-4 border-0 p-4">
            <div class="text-center mb-4">
                <span class="d-inline-grid place-items-center bg-primary text-white rounded-4 mb-3" style="width:3.5rem;height:3.5rem;"><i class="bi bi-building fs-4"></i></span>
                <h1 class="h4 fw-bold mb-1">Houm <span class="text-primary">Bogotá</span></h1>
                <p class="text-muted mb-0">Inicia sesión en tu cuenta</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-4" role="alert" style="--bs-alert-color:#EF4444;--bs-alert-border-color:#EF4444;">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <span>Correo o contraseña incorrectos</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?url=login" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input class="form-control" id="email" name="email" type="email" placeholder="tu@correo.com" autocomplete="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input class="form-control" id="password" name="password" type="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" id="recordarme" name="recordarme" type="checkbox" value="1">
                        <label class="form-check-label" for="recordarme">Recordarme</label>
                    </div>
                    <a href="index.php?controller=auth&action=recuperar">¿Olvidaste tu contraseña?</a>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Iniciar sesión</button>
            </form>
            <p class="text-center text-muted small mb-0 mt-4">¿No tienes cuenta? <a href="index.php?controller=auth&action=registro">Crear cuenta</a></p>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    togglePassword.addEventListener('click', () => {
        const mostrar = password.type === 'password';
        password.type = mostrar ? 'text' : 'password';
        togglePassword.querySelector('i').className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
        togglePassword.setAttribute('aria-pressed', mostrar);
        togglePassword.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
</script>
</body>
</html>
