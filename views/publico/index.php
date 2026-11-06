<?php
require_once __DIR__ . '/../../config/imagenes.php';
$inmuebles = $inmuebles ?? [];
$filtros = $filtros ?? [];
$ciudades = $ciudades ?? ['Bogotá'];
$usuario = $usuario ?? null;
$beneficios = [
    ['bi-grid', 'Variedad de inmuebles', 'Apartamentos, casas, locales y bodegas en toda Bogotá.'],
    ['bi-shield-check', 'Seguridad', 'Propiedades verificadas y contratos con llave única.'],
    ['bi-headset', 'Soporte', 'Acompañamiento de un asesor Houmer antes y después.'],
    ['bi-patch-check', 'Confianza', 'Transparencia en precios, fotos y estado del inmueble.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Houm Bogotá | Encuentra el hogar perfecto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="public/css/publico.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-public sticky-top shadow-sm">
    <div class="container">
        <a class="logo-public" href="index.php"><i class="bi bi-building me-2"></i>Houm <span>Bogotá</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPublico" aria-controls="menuPublico" aria-expanded="false" aria-label="Abrir menú"><i class="bi bi-list"></i></button>
        <div class="collapse navbar-collapse" id="menuPublico">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#catalogo">Inmuebles</a></li>
                <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <?php if ($usuario): ?>
                        <a class="btn btn-primary" href="index.php?controller=inmueble&action=dashboard">Mi panel</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary" href="index.php?url=login">Iniciar sesión</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container py-5">
        <h1>Encuentra el hogar perfecto en Bogotá</h1>
        <p class="lead mb-4">Explora inmuebles disponibles verificados por Houm Bogotá, sin registrarte.</p>
        <form method="get" action="index.php" class="card border-0 shadow rounded-4 p-3">
            <input type="hidden" name="controller" value="publico">
            <input type="hidden" name="action" value="index">
            <div class="row g-2">
                <div class="col-md-4"><input class="form-control" name="ciudad" placeholder="Ciudad" value="<?= htmlspecialchars($filtros['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4"><input class="form-control" name="barrio" placeholder="Barrio o dirección" value="<?= htmlspecialchars($filtros['barrio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-3">
                    <select class="form-select" name="tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach (['apartamento', 'casa', 'local', 'bodega'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($filtros['tipo'] ?? '') === $tipo ? 'selected' : '' ?>><?= ucfirst($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i></button></div>
            </div>
        </form>
    </div>
</header>

<main class="container py-5">
    <section class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
        <?php foreach ($beneficios as [$icono, $tituloB, $textoB]): ?>
            <div class="col"><div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <span class="beneficio-icon mx-auto mb-3"><i class="bi <?= $icono ?>"></i></span>
                <h2 class="h6 fw-bold"><?= $tituloB ?></h2><p class="text-muted small mb-0"><?= $textoB ?></p>
            </div></div>
        <?php endforeach; ?>
    </section>

    <section id="catalogo" class="mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0">Inmuebles disponibles</h2>
            <span class="text-muted"><?= count($inmuebles) ?> resultado(s)</span>
        </div>

        <form method="get" action="index.php" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <input type="hidden" name="controller" value="publico">
            <input type="hidden" name="action" value="index">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small" for="f-ciudad">Ubicación</label>
                    <select class="form-select" id="f-ciudad" name="ciudad">
                        <option value="">Todas</option>
                        <?php foreach ($ciudades as $c): ?>
                            <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= ($filtros['ciudad'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="f-tipo">Tipo</label>
                    <select class="form-select" id="f-tipo" name="tipo">
                        <option value="">Todos</option>
                        <?php foreach (['apartamento', 'casa', 'local', 'bodega'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($filtros['tipo'] ?? '') === $tipo ? 'selected' : '' ?>><?= ucfirst($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="f-min">Precio desde</label>
                    <input class="form-control" id="f-min" name="precio_min" type="number" min="0" placeholder="$" value="<?= htmlspecialchars($filtros['precio_min'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="f-max">Precio hasta</label>
                    <input class="form-control" id="f-max" name="precio_max" type="number" min="0" placeholder="$" value="<?= htmlspecialchars($filtros['precio_max'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="f-hab">Habitaciones</label>
                    <select class="form-select" id="f-hab" name="habitaciones_min">
                        <option value="">Cualquiera</option>
                        <?php for ($n = 1; $n <= 5; $n++): ?>
                            <option value="<?= $n ?>" <?= (string) ($filtros['habitaciones_min'] ?? '') === (string) $n ? 'selected' : '' ?>><?= $n ?>+</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search"></i></button>
                    <a class="btn btn-outline-secondary" href="index.php?controller=publico&action=index#catalogo" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>

        <?php if (!$inmuebles): ?>
            <div class="alert alert-info rounded-4 text-center py-4">
                <i class="bi bi-search me-2"></i>No se encontraron inmuebles con los filtros aplicados.
                <a href="index.php?controller=publico&action=index#catalogo">Limpiar filtros</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($inmuebles as $inmueble): ?>
                    <?php
                    $foto = htmlspecialchars(imagenInmueble($inmueble), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col">
                        <article class="card card-inmueble h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <img class="card-img-top" src="<?= $foto ?>" alt="<?= htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h3 class="h6 fw-bold mb-0"><?= htmlspecialchars($inmueble['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="badge bg-primary text-capitalize"><?= htmlspecialchars($inmueble['tipo'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="fw-bold text-primary fs-5 my-2">$<?= number_format((float) ($inmueble['precio_canon'] ?? 0), 0, ',', '.') ?>/mes</div>
                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($inmueble['direccion'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($inmueble['ciudad'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="small text-muted mb-3">
                                    <i class="bi bi-moon-stars me-1"></i><?= (int) $inmueble['habitaciones'] ?>
                                    <i class="bi bi-droplet ms-2 me-1"></i><?= (int) $inmueble['banos'] ?>
                                    <i class="bi bi-rulers ms-2 me-1"></i><?= number_format((float) $inmueble['area_m2'], 0) ?> m²
                                </p>
                                <a class="btn btn-primary w-100 mt-auto" href="index.php?controller=publico&action=detalle&id=<?= (int) $inmueble['id'] ?>">Ver detalle</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="nosotros" class="card border-0 shadow-sm rounded-4 p-4 mb-5">
        <h2 class="h4 fw-bold">Nosotros</h2>
        <p class="text-muted mb-0">Houm Bogotá es un portal PropTech que conecta propietarios, arrendatarios y asesores (Houmers) con tecnología: catálogo verificado, contratos con llave única y acompañamiento en cada visita. Explora sin registrarte y agenda tu visita cuando encuentres tu hogar.</p>
    </section>
</main>

<footer id="contacto" class="footer-public py-5">
    <div class="container row g-4">
        <div class="col-md-4">
            <div class="fw-bold fs-5 mb-2">Houm <span class="text-primary">Bogotá</span></div>
            <p class="small mb-0">Encuentra el hogar perfecto con acompañamiento experto.</p>
        </div>
        <div class="col-md-4">
            <div class="fw-bold mb-2">Contacto</div>
            <p class="small mb-1"><i class="bi bi-geo-alt me-2"></i>Bogotá, Colombia</p>
            <p class="small mb-1"><i class="bi bi-telephone me-2"></i>+57 601 555 0101</p>
            <p class="small mb-0"><i class="bi bi-envelope me-2"></i>contacto@houmbogota.co</p>
        </div>
        <div class="col-md-4">
            <div class="fw-bold mb-2">Enlaces</div>
            <p class="small mb-1"><a href="index.php">Inicio</a></p>
            <p class="small mb-1"><a href="#catalogo">Inmuebles</a></p>
            <p class="small mb-0"><a href="index.php?url=login">Iniciar sesión</a></p>
        </div>
    </div>
    <div class="container small text-muted mt-4">© <?= date('Y') ?> Houm Bogotá · Sistema Inmobiliario</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/publico.js"></script>
</body>
</html>
