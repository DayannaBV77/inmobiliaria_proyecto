<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController
{
    private UsuarioModel $usuarioModel;

    public function __construct(?UsuarioModel $usuarioModel = null)
    {
        $this->usuarioModel = $usuarioModel ?: new UsuarioModel();
        $this->iniciarSesion();
    }

    public static function autenticado(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
        }

        return !empty($_SESSION['usuario']['id']);
    }

    /** Rol actual del usuario en sesión (null si no hay sesión). */
    public static function rolActual(): ?string
    {
        return self::autenticado() ? ($_SESSION['usuario']['rol'] ?? null) : null;
    }

    /**
     * RBAC central: exige sesión activa y que el rol esté en $roles.
     * Sin sesión -> login. Con sesión pero rol no permitido -> dashboard.
     */
    public static function requerirRol(array $roles): void
    {
        if (!self::autenticado()) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        if (!in_array($_SESSION['usuario']['rol'] ?? '', $roles, true)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a este módulo.';
            header('Location: index.php?controller=inmueble&action=dashboard');
            exit;
        }
    }

    public function login(): void
    {
        if (self::autenticado()) {
            $this->redirigir('index.php?controller=inmueble&action=dashboard');
        }

        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $usuario = $this->usuarioModel->validarLogin($email, $password);

            if ($usuario) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = $usuario;
                $this->redirigir('index.php?controller=inmueble&action=dashboard');
            }

            $error = 'Credenciales incorrectas.';
        }

        $this->vista('auth/login.php', ['error' => $error]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !empty($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();
        $this->redirigir('index.php?url=login');
    }

    private function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
        }
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
