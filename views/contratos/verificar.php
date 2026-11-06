<?php
$contrato = $contrato ?? null;
$codigo = $codigo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Contrato | Houm Bogotá</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="public/css/custom.css" rel="stylesheet">
</head>
<body>
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm rounded-4 border-0 p-4 text-center">
            <h1 class="h4 fw-bold mb-3">Verificar <span class="text-primary">Contrato</span></h1>
            <form method="get" action="index.php" class="mb-4">
                <input type="hidden" name="controller" value="contrato">
                <input type="hidden" name="action" value="verificar">
                <div class="input-group">
                    <input class="form-control" name="codigo" placeholder="Código HOUM-XXXXXXXXXXXX" value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>" required>
                    <button class="btn btn-primary" type="submit">Verificar</button>
                </div>
            </form>
            <?php if ($contrato): ?>
                <div class="alert alert-success rounded-4 text-start">
                    <i class="bi bi-check-circle me-2"></i><strong>Contrato válido.</strong><br>
                    Inmueble: <?= htmlspecialchars($contrato['inmueble_titulo'], ENT_QUOTES, 'UTF-8') ?><br>
                    Arrendatario: <?= htmlspecialchars($contrato['arrendatario_nombre'], ENT_QUOTES, 'UTF-8') ?><br>
                    Estado: <?= htmlspecialchars($contrato['estado'], ENT_QUOTES, 'UTF-8') ?> ·
                    Vigencia: <?= htmlspecialchars($contrato['fecha_inicio'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($contrato['fecha_fin'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php elseif ($codigo !== ''): ?>
                <div class="alert alert-danger rounded-4"><i class="bi bi-x-circle me-2"></i>No se encontró un contrato con ese código.</div>
            <?php endif; ?>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
