<?php

// =============================================================================
// FRONT CONTROLLER - Enrutador central MVC (PHP 8+, PDO, sin frameworks)
// Esquemas de ruta soportados:
//   1. Raíz "/" o "index.php"            -> Portal público (PublicoController)
//   2. Ruta independiente "?url=login"   -> Login (solo desde el Navbar)
//   3. "?controller=...&action=..."      -> Panel protegido (requiere sesión)
//   4. "?ruta=..." (antiguo)             -> Compatibilidad, se traduce a 3.
// Separación de acceso: las rutas públicas NO exigen sesión; las protegidas
// redirigen a "?url=login" y sus controladores vuelven a verificar con RBAC.
// =============================================================================

// --- 1. Configuración y dependencias ---------------------------------------
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/UsuarioModel.php';
require_once __DIR__ . '/models/InmuebleModel.php';
require_once __DIR__ . '/models/ContratoModel.php';
require_once __DIR__ . '/models/ReporteModel.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/InmuebleController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/ContratoController.php';
require_once __DIR__ . '/controllers/ReporteController.php';
require_once __DIR__ . '/controllers/PublicoController.php';

// --- Utilidad de redirección -------------------------------------------------
function redirigirFront(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// --- 2. Mapa seguro de controladores y acciones permitidas ------------------
// Solo se puede instanciar lo que está en esta lista (lista blanca).
$controladoresPermitidos = [
    'auth' => [
        'clase' => AuthController::class,
        'acciones' => ['login', 'logout'],
    ],
    'inmueble' => [
        'clase' => InmuebleController::class,
        'acciones' => ['dashboard', 'listar', 'registrar', 'detalle'],
    ],
    'usuario' => [
        'clase' => UsuarioController::class,
        'acciones' => ['listar', 'registrar', 'editar', 'eliminar'],
    ],
    'contrato' => [
        'clase' => ContratoController::class,
        'acciones' => ['listar', 'registrar', 'detalle', 'pdf', 'verificar'],
    ],
    'reporte' => [
        'clase' => ReporteController::class,
        'acciones' => ['index', 'dashboard'],
    ],
    'publico' => [
        'clase' => PublicoController::class,
        'acciones' => ['index', 'detalle'],
    ],
];

// Rutas independientes "?url=..." (login solo accesible desde el Navbar).
$rutasUrl = [
    '' => ['publico', 'index'],
    'inicio' => ['publico', 'index'],
    'home' => ['publico', 'index'],
    'catalogo' => ['publico', 'index'],
    'inmuebles' => ['publico', 'index'],
    'detalle' => ['publico', 'detalle'],
    'login' => ['auth', 'login'],
    'logout' => ['auth', 'logout'],
    'dashboard' => ['inmueble', 'dashboard'],
    'panel' => ['inmueble', 'dashboard'],
];

// Compatibilidad: traducir las rutas antiguas ?ruta=... al nuevo formato.
$rutasAntiguas = [
    'login' => ['auth', 'login'],
    'logout' => ['auth', 'logout'],
    'dashboard' => ['inmueble', 'dashboard'],
    'inmuebles' => ['inmueble', 'listar'],
    'inmuebles/registrar' => ['inmueble', 'registrar'],
    'inmuebles/detalle' => ['inmueble', 'detalle'],
    'usuarios' => ['usuario', 'listar'],
    'contratos' => ['contrato', 'listar'],
    'reportes' => ['reporte', 'index'],
];

// Clasificación de acceso (además del RBAC dentro de cada controlador).
$rutasPublicas = ['publico' => ['index', 'detalle'], 'auth' => ['login'], 'contrato' => ['verificar']];
$rutasProtegidas = ['inmueble', 'usuario', 'contrato', 'reporte'];

$controller = strtolower(trim($_GET['controller'] ?? ''));
$action = strtolower(trim($_GET['action'] ?? ''));

if (($controller === '' || $action === '') && array_key_exists('url', $_GET)) {
    $url = strtolower(trim($_GET['url']));
    if (isset($rutasUrl[$url])) {
        [$controller, $action] = $rutasUrl[$url];
    } else {
        redirigirFront('index.php');
    }
}

if (($controller === '' || $action === '') && isset($rutasAntiguas[$_GET['ruta'] ?? ''])) {
    [$controller, $action] = $rutasAntiguas[$_GET['ruta']];
}

// --- 3. Ruta por defecto: la página de inicio es pública ---------------------
$urlLogin = 'index.php?url=login';
$urlDashboard = 'index.php?controller=inmueble&action=dashboard';
$urlInicio = 'index.php';

if ($controller === '' || $action === '') {
    $controller = 'publico';
    $action = 'index';
}

// --- 3b. Primera barrera: rutas protegidas exigen sesión ---------------------
$esPublica = isset($rutasPublicas[$controller]) && in_array($action, $rutasPublicas[$controller], true);
if (in_array($controller, $rutasProtegidas, true) && !$esPublica && !AuthController::autenticado()) {
    redirigirFront($urlLogin);
}

// --- 4. Validación segura de controlador y acción ----------------------------
if (!isset($controladoresPermitidos[$controller])) {
    redirigirFront(AuthController::autenticado() ? $urlDashboard : $urlInicio);
}

$definicion = $controladoresPermitidos[$controller];
$clase = $definicion['clase'];

if (
    !in_array($action, $definicion['acciones'], true)
    || !class_exists($clase)
    || !method_exists($clase, $action)
    || !(new ReflectionMethod($clase, $action))->isPublic()
) {
    redirigirFront(AuthController::autenticado() ? $urlDashboard : $urlInicio);
}

// --- 5. Instanciación dinámica inyectando la conexión PDO --------------------
// Los controladores actuales reciben su modelo, y el modelo recibe el PDO
// único de /config/database.php (sin duplicar la conexión).
$pdo = getPDO();

$usuarioModel = new UsuarioModel($pdo);
$inmuebleModel = new InmuebleModel($pdo);

$controlador = match ($controller) {
    'auth' => new AuthController($usuarioModel),
    'inmueble' => new InmuebleController($inmuebleModel),
    'usuario' => new UsuarioController($usuarioModel),
    'contrato' => new ContratoController(new ContratoModel($pdo), $inmuebleModel, $usuarioModel),
    'reporte' => new ReporteController(new ReporteModel($pdo)),
    'publico' => new PublicoController($inmuebleModel),
    default => redirigirFront(AuthController::autenticado() ? $urlDashboard : $urlInicio),
};

// --- 6. Ejecución de la acción solicitada -----------------------------------
if (in_array($action, ['detalle', 'editar', 'eliminar', 'pdf'], true)) {
    $controlador->{$action}((int) ($_GET['id'] ?? $_POST['id'] ?? 0));
} elseif ($action === 'dashboard' && $controller === 'reporte') {
    $controlador->index();
} else {
    $controlador->{$action}();
}
