<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo de reportes e indicadores del dashboard administrativo.
 * Usa SQL avanzado (subconsultas, agregaciones, CASE y vistas) sobre las
 * tablas contratos, pagos, inmuebles y citas ya existentes.
 */
class ReporteModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: getPDO();
    }

    /** Indicadores clave en una sola consulta (estilo consultas.sql #5). */
    public function indicadores(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM inmuebles) AS total_inmuebles,
                (SELECT COUNT(*) FROM inmuebles WHERE estado = 'DISPONIBLE') AS inmuebles_disponibles,
                (SELECT COUNT(*) FROM inmuebles WHERE estado = 'ARRENDADO') AS inmuebles_arrendados,
                (SELECT COUNT(*) FROM contratos) AS total_contratos,
                (SELECT COUNT(*) FROM contratos WHERE estado = 'ACTIVO') AS contratos_activos,
                (SELECT COUNT(*) FROM contratos WHERE estado = 'INCUMPLIDO') AS contratos_incumplidos,
                (SELECT COUNT(*) FROM citas WHERE estado = 'PROGRAMADA' AND fecha_hora >= NOW()) AS citas_programadas,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'PAGADO') AS recaudo_pagado,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'PENDIENTE') AS cartera_pendiente,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'RECHAZADO') AS monto_rechazado,
                (SELECT COALESCE(AVG(canon_mensual), 0) FROM contratos WHERE estado = 'ACTIVO') AS canon_promedio_activos"
        );
        $r = $stmt->fetch();

        return [
            'total_inmuebles' => (int) $r['total_inmuebles'],
            'inmuebles_disponibles' => (int) $r['inmuebles_disponibles'],
            'inmuebles_arrendados' => (int) $r['inmuebles_arrendados'],
            'total_contratos' => (int) $r['total_contratos'],
            'contratos_activos' => (int) $r['contratos_activos'],
            'contratos_incumplidos' => (int) $r['contratos_incumplidos'],
            'citas_programadas' => (int) $r['citas_programadas'],
            'recaudo_pagado' => (float) $r['recaudo_pagado'],
            'cartera_pendiente' => (float) $r['cartera_pendiente'],
            'monto_rechazado' => (float) $r['monto_rechazado'],
            'canon_promedio_activos' => (float) $r['canon_promedio_activos'],
        ];
    }

    /** Cartera morosa: contratos incumplidos o con vencimientos pasados. */
    public function carteraMorosa(): array
    {
        return $this->pdo->query(
            'SELECT * FROM v_carter_morosa ORDER BY saldo_moroso DESC'
        )->fetchAll();
    }

    /** Detalle de tiempos de colocación por contrato (días publicación→inicio). */
    public function tiemposColocacion(): array
    {
        return $this->pdo->query(
            'SELECT * FROM v_tiempos_colocacion ORDER BY dias_colocacion DESC'
        )->fetchAll();
    }

    /** Promedio de días de colocación agrupado por tipo de inmueble. */
    public function promedioPorTipo(): array
    {
        return $this->pdo->query(
            'SELECT tipo, COUNT(*) AS contratos, ROUND(AVG(dias_colocacion), 1) AS promedio_dias
             FROM v_tiempos_colocacion GROUP BY tipo ORDER BY promedio_dias DESC'
        )->fetchAll();
    }

    /** Promedio de días de colocación agrupado por ciudad. */
    public function promedioPorCiudad(): array
    {
        return $this->pdo->query(
            'SELECT ciudad, COUNT(*) AS contratos, ROUND(AVG(dias_colocacion), 1) AS promedio_dias
             FROM v_tiempos_colocacion GROUP BY ciudad ORDER BY promedio_dias DESC'
        )->fetchAll();
    }

    /** Recaudo PAGADO agrupado por mes (últimos 12 meses con pagos). */
    public function recaudoPorMes(): array
    {
        return $this->pdo->query(
            "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS mes,
                    COUNT(*) AS pagos,
                    COALESCE(SUM(monto), 0) AS total
             FROM pagos WHERE estado = 'PAGADO'
             GROUP BY mes ORDER BY mes DESC LIMIT 12"
        )->fetchAll();
    }
}
