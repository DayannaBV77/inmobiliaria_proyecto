<?php

require_once __DIR__ . '/../config/database.php';

class UsuarioModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: getPDO();
    }

    public function obtenerPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email, password, telefono, rol, created_at, updated_at
             FROM usuarios WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function validarLogin(string $email, string $password): ?array
    {
        $usuario = $this->obtenerPorEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return null;
        }

        unset($usuario['password']);

        return $usuario;
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email, telefono, rol, created_at, updated_at
             FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public const ROLES = ['administrador', 'houmer', 'propietario', 'arrendatario'];

    /** Lista usuarios (sin password) con filtros opcionales de rol y búsqueda. */
    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (in_array($filtros['rol'] ?? '', self::ROLES, true)) {
            $where[] = 'rol = :rol';
            $params['rol'] = $filtros['rol'];
        }

        $busqueda = trim($filtros['busqueda'] ?? '');
        if ($busqueda !== '') {
            $where[] = '(nombre LIKE :busqueda OR email LIKE :busqueda OR telefono LIKE :busqueda)';
            $params['busqueda'] = "%{$busqueda}%";
        }

        $sql = 'SELECT id, nombre, email, telefono, rol, created_at, updated_at FROM usuarios';
        $sql .= $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ORDER BY rol, nombre';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function existeEmail(string $email, ?int $exceptoId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = :email';
        $params = ['email' => $email];

        if ($exceptoId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptoId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** Crea un usuario hasheando la contraseña con password_hash(). */
    public function crear(array $datos): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, email, password, telefono, rol)
             VALUES (:nombre, :email, :password, :telefono, :rol)'
        );
        $stmt->execute([
            'nombre' => trim($datos['nombre']),
            'email' => trim($datos['email']),
            'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
            'telefono' => trim($datos['telefono']),
            'rol' => $datos['rol'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** Actualiza un usuario; si "password" viene vacío, no se modifica. */
    public function actualizar(int $id, array $datos): bool
    {
        $campos = [
            'nombre' => trim($datos['nombre']),
            'email' => trim($datos['email']),
            'telefono' => trim($datos['telefono']),
            'rol' => $datos['rol'],
        ];

        if (!empty($datos['password'])) {
            $campos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        }

        $set = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($campos)));
        $stmt = $this->pdo->prepare("UPDATE usuarios SET {$set} WHERE id = :id");
        $campos['id'] = $id;

        return $stmt->execute($campos);
    }

    /** Elimina un usuario. Lanza PDOException si tiene FK asociadas (RESTRICT). */
    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
