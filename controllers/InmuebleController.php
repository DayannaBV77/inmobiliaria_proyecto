<?php

require_once __DIR__ . '/../models/InmuebleModel.php';
require_once __DIR__ . '/AuthController.php';

class InmuebleController
{
    private InmuebleModel $inmuebleModel;

    public function __construct(?InmuebleModel $inmuebleModel = null)
    {
        $this->inmuebleModel = $inmuebleModel ?: new InmuebleModel();
    }

    public function dashboard(): void
    {
        $this->requerirAuth();
        $kpis = $this->inmuebleModel->obtenerKPIs();

        $this->vista('dashboard/index.php', [
            'kpis' => $kpis,
            'usuario' => $_SESSION['usuario'],
        ]);
    }

    public function listar(): void
    {
        $this->requerirAuth();

        $filtros = [
            'estado' => trim($_GET['estado'] ?? ''),
            'zona' => trim($_GET['zona'] ?? ''),
            'busqueda' => trim($_GET['busqueda'] ?? ''),
        ];
        $inmuebles = $this->inmuebleModel->listar($filtros);

        $this->vista('inmuebles/listado.php', [
            'inmuebles' => $inmuebles,
            'filtros' => $filtros,
            'usuario' => $_SESSION['usuario'],
        ]);
    }

    public function registrar(): void
    {
        $this->requerirAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->vista('inmuebles/registrar.php', ['usuario' => $_SESSION['usuario']]);
        }

        $propietarioId = (int) ($_POST['propietario_id'] ?? (($_SESSION['usuario']['rol'] ?? '') === 'propietario' ? $_SESSION['usuario']['id'] : 0));
        $tipo = $_POST['tipo'] ?? '';
        $estrato = (int) ($_POST['estrato'] ?? 0);
        $areaM2 = (float) ($_POST['area_m2'] ?? 0);

        $tiposValidos = ['apartamento', 'casa', 'local', 'bodega'];
        $estadosValidos = ['DISPONIBLE', 'EN_PROCESO_RESERVA', 'RESERVADO', 'ARRENDADO', 'MANTENIMIENTO'];
        $estado = strtoupper(trim($_POST['estado'] ?? 'DISPONIBLE'));

        if (
            $propietarioId <= 0
            || trim($_POST['titulo'] ?? '') === ''
            || trim($_POST['descripcion'] ?? '') === ''
            || !in_array($tipo, $tiposValidos, true)
            || trim($_POST['direccion'] ?? '') === ''
            || $estrato < 1 || $estrato > 6
            || $areaM2 <= 0
            || !in_array($estado, $estadosValidos, true)
        ) {
            $_SESSION['error'] = 'Verifica los datos del inmueble: propietario, título, descripción, tipo, dirección, estrato (1-6) y área.';
            $this->redirigir('index.php?controller=inmueble&action=registrar');
        }

        $imagen = $this->subirImagen($_FILES['imagen'] ?? null);

        if ($imagen === false) {
            $_SESSION['error'] = 'La imagen no es válida. Usa JPG, PNG o WebP de máximo 5 MB.';
            $this->redirigir('index.php?controller=inmueble&action=registrar');
        }

        $id = $this->inmuebleModel->insertar([
            'propietario_id' => $propietarioId,
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'tipo' => $tipo,
            'precio_canon' => ($_POST['precio_canon'] ?? '') !== '' ? (float) $_POST['precio_canon'] : null,
            'precio_venta' => ($_POST['precio_venta'] ?? '') !== '' ? (float) $_POST['precio_venta'] : null,
            'direccion' => $_POST['direccion'],
            'ciudad' => $_POST['ciudad'] ?? 'Bogotá',
            'estrato' => $estrato,
            'habitaciones' => (int) ($_POST['habitaciones'] ?? 1),
            'banos' => (int) ($_POST['banos'] ?? 1),
            'area_m2' => $areaM2,
            'estado' => $estado,
        ]);

        if ($imagen) {
            $_SESSION['imagen_inmueble'] = $imagen;
        }

        $_SESSION['exito'] = 'Inmueble registrado correctamente.';
        $this->redirigir("index.php?controller=inmueble&action=detalle&id={$id}");
    }

    public function detalle(?int $id = null): void
    {
        $this->requerirAuth();
        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $inmueble = $id > 0 ? $this->inmuebleModel->obtenerPorId($id) : null;

        if (!$inmueble) {
            $_SESSION['error'] = 'Inmueble no encontrado.';
            $this->redirigir('index.php?controller=inmueble&action=listar');
        }

        $this->vista('inmuebles/detalle.php', [
            'inmueble' => $inmueble,
            'usuario' => $_SESSION['usuario'],
        ]);
    }

    private function requerirAuth(): void
    {
        if (!AuthController::autenticado()) {
            $this->redirigir('index.php?controller=auth&action=login');
        }
    }

    private function subirImagen(?array $archivo): string|false|null
    {
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK || ($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
            return false;
        }

        $info = getimagesize($archivo['tmp_name']);
        $mimesValidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!$info || !isset($mimesValidos[$info['mime']])) {
            return false;
        }

        $destino = __DIR__ . '/../public/uploads';

        if (!is_dir($destino) && !mkdir($destino, 0755, true)) {
            return false;
        }

        $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $mimesValidos[$info['mime']];

        if (!move_uploaded_file($archivo['tmp_name'], $destino . '/' . $nombre)) {
            return false;
        }

        return $nombre;
    }

    private function vista(string $archivo, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);
        $rutaVista = __DIR__ . '/../views/' . $archivo;

        if (!is_file($rutaVista)) {
            throw new RuntimeException("Vista no encontrada: {$archivo}");
        }

        require $rutaVista;
    }

    private function redirigir(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
