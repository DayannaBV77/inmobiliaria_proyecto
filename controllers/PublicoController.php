<?php

require_once __DIR__ . '/../models/InmuebleModel.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Portal público de Houm Bogotá: sin autenticación.
 * Solo expone inmuebles DISPONIBLE. Las rutas administrativas
 * (Inmueble/Usuario/Contrato/Reporte) siguen protegidas por sesión/RBAC.
 */
class PublicoController
{
    private InmuebleModel $inmuebleModel;

    public function __construct(?InmuebleModel $inmuebleModel = null)
    {
        $this->inmuebleModel = $inmuebleModel ?: new InmuebleModel();
    }

    /** Página de inicio pública + catálogo con filtros (GET). */
    public function index(): void
    {
        $filtros = [
            'ciudad' => trim($_GET['ciudad'] ?? ''),
            'barrio' => trim($_GET['barrio'] ?? ''),
            'tipo' => trim($_GET['tipo'] ?? ''),
            'precio_min' => trim($_GET['precio_min'] ?? ''),
            'precio_max' => trim($_GET['precio_max'] ?? ''),
            'habitaciones_min' => trim($_GET['habitaciones_min'] ?? $_GET['habitaciones'] ?? ''),
            'busqueda' => trim($_GET['busqueda'] ?? ''),
        ];

        $this->vista('publico/index.php', [
            'inmuebles' => $this->inmuebleModel->listarPublico($filtros),
            'filtros' => $filtros,
            'ciudades' => $this->inmuebleModel->ciudades(),
            'usuario' => AuthController::autenticado() ? $_SESSION['usuario'] : null,
        ]);
    }

    /** Detalle público: solo si el inmueble está DISPONIBLE. */
    public function detalle(?int $id = null): void
    {
        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $inmueble = $id > 0 ? $this->inmuebleModel->obtenerPorId($id) : null;

        if (!$inmueble || $inmueble['estado'] !== 'DISPONIBLE') {
            header('Location: index.php');
            exit;
        }

        $this->vista('publico/detalle.php', [
            'inmueble' => $inmueble,
            'usuario' => AuthController::autenticado() ? $_SESSION['usuario'] : null,
        ]);
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
}
