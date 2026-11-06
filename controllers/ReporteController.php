<?php

require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Reportes e indicadores (dashboard administrativo).
 * Solo administrador: cartera morosa, tiempos de colocación y recaudo.
 */
class ReporteController
{
    private ReporteModel $reporteModel;

    public function __construct(?ReporteModel $reporteModel = null)
    {
        $this->reporteModel = $reporteModel ?: new ReporteModel();
    }

    public function index(): void
    {
        AuthController::requerirRol(['administrador']);

        $this->vista('reportes/index.php', [
            'usuario' => $_SESSION['usuario'],
            'indicadores' => $this->reporteModel->indicadores(),
            'cartera' => $this->reporteModel->carteraMorosa(),
            'colocacion' => $this->reporteModel->tiemposColocacion(),
            'porTipo' => $this->reporteModel->promedioPorTipo(),
            'porCiudad' => $this->reporteModel->promedioPorCiudad(),
            'recaudoMes' => $this->reporteModel->recaudoPorMes(),
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
