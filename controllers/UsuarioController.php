<?php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/AuthController.php';

/**
 * Gestión de usuarios con RBAC.
 * - administrador: CRUD completo.
 * - houmer: solo lectura del listado.
 * - propietario / arrendatario: sin acceso (vuelven al dashboard).
 */
class UsuarioController
{
    private UsuarioModel $usuarioModel;

    public function __construct(?UsuarioModel $usuarioModel = null)
    {
        $this->usuarioModel = $usuarioModel ?: new UsuarioModel();
    }

    public function listar(): void
    {
        AuthController::requerirRol(['administrador', 'houmer']);

        $filtros = [
            'rol' => trim($_GET['rol'] ?? ''),
            'busqueda' => trim($_GET['busqueda'] ?? ''),
        ];
        $usuarios = $this->usuarioModel->listar($filtros);

        $this->vista('usuarios/listado.php', [
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'usuario' => $_SESSION['usuario'],
            'esAdmin' => AuthController::rolActual() === 'administrador',
        ]);
    }

    /** GET: formulario. POST: crea el usuario (solo administrador). */
    public function registrar(): void
    {
        AuthController::requerirRol(['administrador']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->vista('usuarios/registrar.php', ['usuario' => $_SESSION['usuario']]);
        }

        $datos = $this->datosValidados();

        if ($datos === null) {
            $this->redirigir('index.php?controller=usuario&action=registrar');
        }

        if ($this->usuarioModel->existeEmail($datos['email'])) {
            $_SESSION['error'] = 'Ya existe un usuario con ese correo.';
            $this->redirigir('index.php?controller=usuario&action=registrar');
        }

        $this->usuarioModel->crear($datos);
        $_SESSION['exito'] = 'Usuario creado correctamente.';
        $this->redirigir('index.php?controller=usuario&action=listar');
    }

    /** GET: formulario precargado. POST: actualiza (password opcional). */
    public function editar(?int $id = null): void
    {
        AuthController::requerirRol(['administrador']);
        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $usuarioEditado = $id > 0 ? $this->usuarioModel->obtenerPorId($id) : null;

        if (!$usuarioEditado) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            $this->redirigir('index.php?controller=usuario&action=listar');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->vista('usuarios/editar.php', [
                'usuario' => $_SESSION['usuario'],
                'editado' => $usuarioEditado,
            ]);
        }

        $datos = $this->datosValidados(false);

        if ($datos === null) {
            $this->redirigir("index.php?controller=usuario&action=editar&id={$id}");
        }

        $datos['password'] = trim($_POST['password'] ?? '');
        $this->usuarioModel->actualizar($id, $datos);
        $_SESSION['exito'] = 'Usuario actualizado correctamente.';
        $this->redirigir('index.php?controller=usuario&action=listar');
    }

    /** Elimina por POST (formulario de la vista). Solo administrador. */
    public function eliminar(?int $id = null): void
    {
        AuthController::requerirRol(['administrador']);
        $id = $id ?: (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

        try {
            $this->usuarioModel->eliminar($id);
            $_SESSION['exito'] = 'Usuario eliminado correctamente.';
        } catch (PDOException) {
            $_SESSION['error'] = 'No se puede eliminar: el usuario tiene registros asociados.';
        }

        $this->redirigir('index.php?controller=usuario&action=listar');
    }

    /** Valida los campos comunes de registrar/editar. Null = error (mensaje en sesión). */
    private function datosValidados(bool $exigirPassword = true): ?array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rol = trim($_POST['rol'] ?? '');
        $password = $_POST['password'] ?? '';

        if (
            $nombre === '' || $telefono === ''
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !in_array($rol, UsuarioModel::ROLES, true)
            || ($exigirPassword && strlen($password) < 8)
        ) {
            $_SESSION['error'] = 'Verifica nombre, correo válido, teléfono, rol y contraseña (mín. 8 caracteres).';
            return null;
        }

        return [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'rol' => $rol,
            'password' => $password,
        ];
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
