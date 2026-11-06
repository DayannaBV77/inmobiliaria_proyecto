<?php

require_once __DIR__ . '/../models/ContratoModel.php';
require_once __DIR__ . '/../models/InmuebleModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Gestión de contratos de arrendamiento.
 * - listar/registrar: administrador y houmer.
 * - detalle/pdf: administrador, houmer y el arrendatario/propietario dueño.
 * - verificar: pública, por llave única (sin sesión).
 * El PDF se genera en PHP puro (sin librerías externas) para XAMPP.
 */
class ContratoController
{
    private ContratoModel $contratoModel;
    private InmuebleModel $inmuebleModel;
    private UsuarioModel $usuarioModel;

    public function __construct(
        ?ContratoModel $contratoModel = null,
        ?InmuebleModel $inmuebleModel = null,
        ?UsuarioModel $usuarioModel = null
    ) {
        $this->contratoModel = $contratoModel ?: new ContratoModel();
        $this->inmuebleModel = $inmuebleModel ?: new InmuebleModel();
        $this->usuarioModel = $usuarioModel ?: new UsuarioModel();
    }

    public function listar(): void
    {
        AuthController::requerirRol(['administrador', 'houmer']);

        $filtros = [
            'estado' => trim($_GET['estado'] ?? ''),
            'busqueda' => trim($_GET['busqueda'] ?? ''),
        ];

        $this->vista('contratos/listado.php', [
            'contratos' => $this->contratoModel->listar($filtros),
            'filtros' => $filtros,
            'usuario' => $_SESSION['usuario'],
        ]);
    }

    /** GET: formulario (inmuebles disponibles + arrendatarios). POST: crea. */
    public function registrar(): void
    {
        AuthController::requerirRol(['administrador', 'houmer']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->vista('contratos/registrar.php', [
                'usuario' => $_SESSION['usuario'],
                'inmuebles' => $this->inmuebleModel->listar(['estado' => 'DISPONIBLE']),
                'arrendatarios' => $this->usuarioModel->listar(['rol' => 'arrendatario']),
            ]);
        }

        $inmuebleId = (int) ($_POST['inmueble_id'] ?? 0);
        $arrendatarioId = (int) ($_POST['arrendatario_id'] ?? 0);
        $inicio = trim($_POST['fecha_inicio'] ?? '');
        $fin = trim($_POST['fecha_fin'] ?? '');
        $canon = (float) ($_POST['canon_mensual'] ?? 0);
        $estado = strtoupper(trim($_POST['estado'] ?? 'ACTIVO'));

        if (
            $inmuebleId <= 0 || $arrendatarioId <= 0
            || $inicio === '' || $fin === '' || $fin < $inicio
            || $canon <= 0 || !in_array($estado, ContratoModel::ESTADOS, true)
        ) {
            $_SESSION['error'] = 'Verifica inmueble, arrendatario, fechas (fin ≥ inicio), canon y estado.';
            $this->redirigir('index.php?controller=contrato&action=registrar');
        }

        $creado = $this->contratoModel->crear([
            'inmueble_id' => $inmuebleId,
            'arrendatario_id' => $arrendatarioId,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'canon_mensual' => $canon,
            'estado' => $estado,
        ]);

        $_SESSION['exito'] = 'Contrato creado. Código de verificación: ' . $creado['codigo_verificacion'];
        $this->redirigir("index.php?controller=contrato&action=detalle&id={$creado['id']}");
    }

    public function detalle(?int $id = null): void
    {
        $contrato = $this->contratoAutenticado($id);

        $this->vista('contratos/detalle.php', [
            'contrato' => $contrato,
            'usuario' => $_SESSION['usuario'],
        ]);
    }

    /** Descarga/visualiza el contrato en PDF (generado en memoria). */
    public function pdf(?int $id = null): void
    {
        $contrato = $this->contratoAutenticado($id);
        $pdf = $this->generarPdf($contrato);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="contrato-' . (int) $contrato['id'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    /** Verificación pública por llave única: ?controller=contrato&action=verificar&codigo=HOUM-... */
    public function verificar(): void
    {
        $codigo = trim($_GET['codigo'] ?? '');
        $contrato = $codigo !== '' ? $this->contratoModel->obtenerPorCodigo($codigo) : null;

        $this->vista('contratos/verificar.php', ['contrato' => $contrato, 'codigo' => $codigo]);
    }

    /** Carga el contrato validando sesión y permiso sobre ese registro. */
    private function contratoAutenticado(?int $id): array
    {
        if (!AuthController::autenticado()) {
            $this->redirigir('index.php?controller=auth&action=login');
        }

        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $contrato = $id > 0 ? $this->contratoModel->obtenerPorId($id) : null;

        if (!$contrato) {
            $_SESSION['error'] = 'Contrato no encontrado.';
            $this->redirigir('index.php?controller=contrato&action=listar');
        }

        $rol = AuthController::rolActual();
        $esDueno = (int) ($_SESSION['usuario']['id'] ?? 0) === (int) $contrato['arrendatario_id'];

        if (!in_array($rol, ['administrador', 'houmer'], true) && !$esDueno) {
            $_SESSION['error'] = 'No tienes permiso para ver este contrato.';
            $this->redirigir('index.php?controller=inmueble&action=dashboard');
        }

        return $contrato;
    }

    /** PDF mínimo válido en PHP puro (Helvetica, A4). Sin dependencias. */
    private function generarPdf(array $c): string
    {
        $texto = fn(string $s): string => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) (iconv('UTF-8', 'WINDOWS-1252//IGNORE', $s) ?: $s));
        $linea = fn(string $s, int $y, int $size = 11, string $font = 'F1'): string => "BT /{$font} {$size} Tf 50 {$y} Td (" . $texto($s) . ") Tj ET\n";

        $contenido = ''
            . $linea('HOUM BOGOTA - CONTRATO DE ARRENDAMIENTO', 800, 16, 'F2')
            . $linea('Codigo de verificacion: ' . ($c['codigo_verificacion'] ?? 'N/A'), 768, 12, 'F2')
            . $linea('ID de contrato: ' . $c['id'], 744)
            . $linea('Estado: ' . $c['estado'], 726)
            . $linea('Inmueble: ' . ($c['inmueble_titulo'] ?? ''), 698)
            . $linea('Direccion: ' . ($c['direccion'] ?? '') . ', ' . ($c['ciudad'] ?? ''), 680)
            . $linea('Arrendatario: ' . ($c['arrendatario_nombre'] ?? ''), 652)
            . $linea('Propietario: ' . ($c['propietario_nombre'] ?? ''), 634)
            . $linea('Fecha de inicio: ' . $c['fecha_inicio'], 606)
            . $linea('Fecha de terminacion: ' . $c['fecha_fin'], 588)
            . $linea('Canon mensual: $' . number_format((float) $c['canon_mensual'], 2), 560, 12, 'F2')
            . $linea('Este documento se genera electronicamente y puede verificarse', 520, 9)
            . $linea('con el codigo de verificacion indicado en la plataforma.', 506, 9);

        $objetos = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            '<< /Length ' . strlen($contenido) . " >>\nstream\n{$contenido}endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objetos as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$obj}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 7\n0000000000 65535 f \n";

        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }

        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
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
