<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo de contratos de arrendamiento.
 * Genera la llave única de verificación (codigo_verificacion) y, al crear un
 * contrato ACTIVO, marca el inmueble como ARRENDADO dentro de una transacción.
 */
class ContratoModel
{
    private PDO $pdo;

    public const ESTADOS = ['ACTIVO', 'FINALIZADO', 'INCUMPLIDO'];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: getPDO();
    }

    /** Lista contratos con inmueble, arrendatario y propietario (JOIN). */
    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (in_array($filtros['estado'] ?? '', self::ESTADOS, true)) {
            $where[] = 'ct.estado = :estado';
            $params['estado'] = $filtros['estado'];
        }

        $busqueda = trim($filtros['busqueda'] ?? '');
        if ($busqueda !== '') {
            $where[] = '(i.titulo LIKE :busqueda OR a.nombre LIKE :busqueda OR ct.codigo_verificacion LIKE :busqueda)';
            $params['busqueda'] = "%{$busqueda}%";
        }

        $sql = 'SELECT ct.*, i.titulo AS inmueble_titulo, a.nombre AS arrendatario_nombre,
                       p.nombre AS propietario_nombre
                FROM contratos ct
                INNER JOIN inmuebles i ON i.id = ct.inmueble_id
                INNER JOIN usuarios a ON a.id = ct.arrendatario_id
                INNER JOIN usuarios p ON p.id = i.propietario_id';
        $sql .= $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ORDER BY ct.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.*, i.titulo AS inmueble_titulo, i.direccion, i.ciudad,
                    a.nombre AS arrendatario_nombre, a.email AS arrendatario_email, a.telefono AS arrendatario_telefono,
                    p.nombre AS propietario_nombre, p.email AS propietario_email
             FROM contratos ct
             INNER JOIN inmuebles i ON i.id = ct.inmueble_id
             INNER JOIN usuarios a ON a.id = ct.arrendatario_id
             INNER JOIN usuarios p ON p.id = i.propietario_id
             WHERE ct.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $contrato = $stmt->fetch();

        return $contrato ?: null;
    }

    /** Busca un contrato por su llave de verificación (endpoint público). */
    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ct.id, ct.codigo_verificacion, ct.estado, ct.fecha_inicio, ct.fecha_fin,
                    i.titulo AS inmueble_titulo, a.nombre AS arrendatario_nombre
             FROM contratos ct
             INNER JOIN inmuebles i ON i.id = ct.inmueble_id
             INNER JOIN usuarios a ON a.id = ct.arrendatario_id
             WHERE ct.codigo_verificacion = :codigo LIMIT 1'
        );
        $stmt->execute(['codigo' => trim($codigo)]);
        $contrato = $stmt->fetch();

        return $contrato ?: null;
    }

    /** Genera una llave única tipo HOUM-XXXXXXXXXXXX (12 hex mayúsculas). */
    public function generarCodigo(): string
    {
        do {
            $codigo = 'HOUM-' . strtoupper(bin2hex(random_bytes(6)));
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM contratos WHERE codigo_verificacion = :codigo');
            $stmt->execute(['codigo' => $codigo]);
        } while ((int) $stmt->fetchColumn() > 0);

        return $codigo;
    }

    /**
     * Crea el contrato y, si queda ACTIVO, pasa el inmueble a ARRENDADO.
     * Todo dentro de una transacción: o se guarda todo o no se guarda nada.
     * Retorna ['id' => int, 'codigo_verificacion' => string].
     */
    public function crear(array $datos): array
    {
        $codigo = $this->generarCodigo();

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO contratos
                    (inmueble_id, arrendatario_id, fecha_inicio, fecha_fin, canon_mensual, codigo_verificacion, estado)
                 VALUES
                    (:inmueble_id, :arrendatario_id, :fecha_inicio, :fecha_fin, :canon_mensual, :codigo_verificacion, :estado)'
            );
            $stmt->execute([
                'inmueble_id' => (int) $datos['inmueble_id'],
                'arrendatario_id' => (int) $datos['arrendatario_id'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'canon_mensual' => (float) $datos['canon_mensual'],
                'codigo_verificacion' => $codigo,
                'estado' => $datos['estado'],
            ]);
            $id = (int) $this->pdo->lastInsertId();

            if ($datos['estado'] === 'ACTIVO') {
                $upd = $this->pdo->prepare("UPDATE inmuebles SET estado = 'ARRENDADO' WHERE id = :id");
                $upd->execute(['id' => (int) $datos['inmueble_id']]);
            }

            $this->pdo->commit();

            return ['id' => $id, 'codigo_verificacion' => $codigo];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE contratos SET estado = :estado WHERE id = :id');

        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }
}
