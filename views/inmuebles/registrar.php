<?php
$titulo = 'Nuevo Inmueble | Houm Bogotá';
$rutaActiva = 'inmuebles';
$error = $error ?? ($_SESSION['error'] ?? null);
unset($_SESSION['error']);
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="h4 fw-bold mb-4">Registrar Inmueble</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger rounded-4" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="index.php?controller=inmueble&action=registrar" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4 p-4">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="titulo">Título del Inmueble *</label>
                <input class="form-control" id="titulo" name="titulo" type="text" placeholder="Ej. Apartamento Chapinero" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="descripcion">Descripción *</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" placeholder="Describe las características del inmueble..." required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="precio_canon">Precio mensual *</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input class="form-control" id="precio_canon" name="precio_canon" type="number" min="0" step="0.01" placeholder="2500000" required>
                    <span class="input-group-text">/mes</span>
                </div>
            </div>
            <div>
                <label class="form-label" for="estado">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="DISPONIBLE">Disponible</option>
                    <option value="RESERVADO">Reservado</option>
                    <option value="ARRENDADO">Arrendado</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <label class="form-label" for="imagen">Imágenes del inmueble</label>
            <div id="dropzone" class="d-flex flex-column align-items-center justify-content-center text-center rounded-4 p-5 h-100" style="border:2px dashed #6C63FF;background:#F8FAFC;min-height:280px;">
                <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-3"></i>
                <p class="text-muted mb-3">Arrastra las imágenes aquí<br><small>JPG, PNG o WebP · máx. 5 MB</small></p>
                <button class="btn btn-outline-secondary" type="button" id="btnSeleccionar">Seleccionar archivos</button>
                <img id="preview" class="img-fluid rounded-4 d-none mt-3" style="max-height:180px;" alt="Vista previa de la imagen">
                <span id="nombreArchivo" class="small text-muted mt-2"></span>
            </div>
            <input id="imagen" name="imagen" type="file" class="d-none" accept="image/jpeg,image/png,image/webp">
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a class="btn btn-light" href="index.php?controller=inmueble&action=listar">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
</form>

<script>
    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('imagen');
    const preview = document.getElementById('preview');
    const nombreArchivo = document.getElementById('nombreArchivo');
    document.getElementById('btnSeleccionar').addEventListener('click', () => input.click());
    dropzone.addEventListener('click', e => { if (e.target.id === 'dropzone') input.click(); });
    ['dragenter', 'dragover'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.style.background = '#e7f1ff'; }));
    ['dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.style.background = '#F8FAFC'; }));
    dropzone.addEventListener('drop', e => { input.files = e.dataTransfer.files; mostrarPreview(); });
    input.addEventListener('change', mostrarPreview);
    function mostrarPreview() {
        const archivo = input.files[0];
        if (!archivo) return;
        nombreArchivo.textContent = archivo.name;
        preview.src = URL.createObjectURL(archivo);
        preview.classList.remove('d-none');
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
