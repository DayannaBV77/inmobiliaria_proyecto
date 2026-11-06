<?php

require_once __DIR__ . '/../config/database.php';

class InmuebleModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: getPDO();
    }

    public function obtenerKPIs(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM inmuebles) AS total_inmuebles,
                (SELECT COUNT(*) FROM inmuebles WHERE estado = 'DISPONIBLE') AS inmuebles_disponibles,
                (SELECT COUNT(*) FROM inmuebles WHERE estado = 'RESERVADO') AS reservas_activas,
                (SELECT COUNT(*) FROM inmuebles WHERE estado = 'ARRENDADO') AS inmuebles_arrendados,
                (SELECT COUNT(*) FROM contratos WHERE estado = 'ACTIVO') AS contratos_activos,
                (SELECT COUNT(*) FROM contratos) AS total_contratos,
                (SELECT COUNT(*) FROM citas WHERE estado = 'PROGRAMADA' AND fecha_hora >= NOW()) AS citas_programadas,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'PAGADO') AS ingresos_total,
                (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'PENDIENTE') AS cartera_pendiente"
        );
        $kpis = $stmt->fetch();

        return [
            'total_inmuebles' => (int) $kpis['total_inmuebles'],
            'inmuebles_disponibles' => (int) $kpis['inmuebles_disponibles'],
            'reservas_activas' => (int) $kpis['reservas_activas'],
            'inmuebles_arrendados' => (int) $kpis['inmuebles_arrendados'],
            'contratos_activos' => (int) $kpis['contratos_activos'],
            'total_contratos' => (int) $kpis['total_contratos'],
            'citas_programadas' => (int) $kpis['citas_programadas'],
            'ingresos_total' => (float) $kpis['ingresos_total'],
            'cartera_pendiente' => (float) $kpis['cartera_pendiente'],
        ];
    }

    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        $estadosValidos = ['DISPONIBLE', 'RESERVADO', 'ARRENDADO', 'MANTENIMIENTO'];
        $estado = strtoupper(trim($filtros['estado'] ?? ''));
        if ($estado !== '' && in_array($estado, $estadosValidos, true)) {
            $where[] = 'i.estado = :estado';
            $params['estado'] = $estado;
        }

        $zona = trim($filtros['zona'] ?? $filtros['ciudad'] ?? '');
        if ($zona !== '') {
            $where[] = 'i.ciudad LIKE :zona';
            $params['zona'] = "%{$zona}%";
        }

        $busqueda = trim($filtros['busqueda'] ?? $filtros['q'] ?? '');
        if ($busqueda !== '') {
            $where[] = '(i.titulo LIKE :busqueda OR i.direccion LIKE :busqueda OR i.descripcion LIKE :busqueda OR i.ciudad LIKE :busqueda)';
            $params['busqueda'] = "%{$busqueda}%";
        }

        $sql = 'SELECT i.*, u.nombre AS propietario_nombre, u.telefono AS propietario_telefono, u.email AS propietario_email
                FROM inmuebles i
                INNER JOIN usuarios u ON i.propietario_id = u.id';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** Catálogo público: solo inmuebles DISPONIBLE, con filtros de portal. */
    public function listarPublico(array $filtros = []): array
    {
        $where = ["i.estado = 'DISPONIBLE'"];
        $params = [];

        $ciudad = trim($filtros['ciudad'] ?? '');
        if ($ciudad !== '') {
            $where[] = 'i.ciudad LIKE :ciudad';
            $params['ciudad'] = "%{$ciudad}%";
        }

        $barrio = trim($filtros['barrio'] ?? '');
        if ($barrio !== '') {
            $where[] = '(i.direccion LIKE :barrio OR i.titulo LIKE :barrio)';
            $params['barrio'] = "%{$barrio}%";
        }

        $tiposValidos = ['apartamento', 'casa', 'local', 'bodega'];
        $tipo = trim($filtros['tipo'] ?? '');
        if (in_array($tipo, $tiposValidos, true)) {
            $where[] = 'i.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        $precioMin = (float) ($filtros['precio_min'] ?? 0);
        if ($precioMin > 0) {
            $where[] = 'i.precio_canon >= :precio_min';
            $params['precio_min'] = $precioMin;
        }

        $precioMax = (float) ($filtros['precio_max'] ?? 0);
        if ($precioMax > 0) {
            $where[] = 'i.precio_canon <= :precio_max';
            $params['precio_max'] = $precioMax;
        }

        $habitaciones = (int) ($filtros['habitaciones_min'] ?? $filtros['habitaciones'] ?? 0);
        if ($habitaciones > 0) {
            $where[] = 'i.habitaciones >= :habitaciones';
            $params['habitaciones'] = $habitaciones;
        }

        $busqueda = trim($filtros['busqueda'] ?? '');
        if ($busqueda !== '') {
            $where[] = '(i.titulo LIKE :busqueda OR i.descripcion LIKE :busqueda OR i.direccion LIKE :busqueda)';
            $params['busqueda'] = "%{$busqueda}%";
        }

        $stmt = $this->pdo->prepare(
            'SELECT i.*, u.nombre AS propietario_nombre, u.telefono AS propietario_telefono
             FROM inmuebles i
             INNER JOIN usuarios u ON i.propietario_id = u.id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY i.created_at DESC'
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** Ciudades con inmuebles disponibles (para el filtro de ubicación). */
    public function ciudades(): array
    {
        return $this->pdo->query(
            "SELECT DISTINCT ciudad FROM inmuebles WHERE estado = 'DISPONIBLE' ORDER BY ciudad"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, u.nombre AS propietario_nombre, u.telefono AS propietario_telefono, u.email AS propietario_email
             FROM inmuebles i
             INNER JOIN usuarios u ON i.propietario_id = u.id
             WHERE i.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $inmueble = $stmt->fetch();

        return $inmueble ?: null;
    }

    public function insertar(array $datos): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO inmuebles
                (propietario_id, titulo, descripcion, tipo, precio_canon, precio_venta, direccion, ciudad, estrato, habitaciones, banos, area_m2, estado)
             VALUES
                (:propietario_id, :titulo, :descripcion, :tipo, :precio_canon, :precio_venta, :direccion, :ciudad, :estrato, :habitaciones, :banos, :area_m2, :estado)'
        );
        $stmt->execute([
            'propietario_id' => (int) $datos['propietario_id'],
            'titulo' => trim($datos['titulo']),
            'descripcion' => trim($datos['descripcion']),
            'tipo' => $datos['tipo'],
            'precio_canon' => $datos['precio_canon'] ?? null,
            'precio_venta' => $datos['precio_venta'] ?? null,
            'direccion' => trim($datos['direccion']),
            'ciudad' => trim($datos['ciudad'] ?? 'Bogotá'),
            'estrato' => (int) $datos['estrato'],
            'habitaciones' => (int) ($datos['habitaciones'] ?? 1),
            'banos' => (int) ($datos['banos'] ?? 1),
            'area_m2' => (float) $datos['area_m2'],
            'estado' => strtoupper($datos['estado'] ?? 'DISPONIBLE'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
