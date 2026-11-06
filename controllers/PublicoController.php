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
        // Sincronización en tiempo real: liberar vencidas antes de listar.
        $this->inmuebleModel->expirarReservasVencidas();

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

    /**
     * Postulación/reserva temporal (POST desde el detalle público).
     * Exige sesión: sin sesión -> login; el modelo bloquea con FOR UPDATE.
     */
    public function reservar(?int $id = null): void
    {
        $id = $id ?: (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        if (!AuthController::autenticado()) {
            header('Location: index.php?url=login');
            exit;
        }

        if (!in_array(AuthController::rolActual(), ['arrendatario', 'administrador'], true)) {
            $_SESSION['error'] = 'Tu rol no puede iniciar reservas.';
            header('Location: index.php');
            exit;
        }

        $resultado = $this->inmuebleModel->iniciarReserva($id, (int) $_SESSION['usuario']['id']);

        if ($resultado['ok']) {
            $_SESSION['exito'] = 'Reserva iniciada. Tienes 24 horas para confirmarla (vence: ' . $resultado['expira'] . ').';
        } else {
            $_SESSION['error'] = $resultado['motivo'] === 'no_encontrado'
                ? 'El inmueble ya no existe.'
                : 'Este inmueble ya no está disponible: otro cliente inició una reserva. Intenta con otro.';
        }

        header('Location: index.php');
        exit;
    }

    /** Confirma la reserva propia (EN_PROCESO -> RESERVADO) antes del vencimiento. */
    public function confirmar(?int $id = null): void
    {
        $id = $id ?: (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        if (!AuthController::autenticado()) {
            header('Location: index.php?url=login');
            exit;
        }

        $resultado = $this->inmuebleModel->confirmarReserva(
            $id,
            (int) $_SESSION['usuario']['id'],
            AuthController::rolActual() === 'administrador'
        );

        $_SESSION[$resultado['ok'] ? 'exito' : 'error'] = $resultado['ok']
            ? 'Reserva confirmada. El inmueble quedó RESERVADO.'
            : ($resultado['motivo'] === 'expirada'
                ? 'Tu reserva venció y el inmueble volvió a estar disponible.'
                : 'No pudimos confirmar: el inmueble ya no está en proceso de reserva.');

        header('Location: index.php');
        exit;
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
