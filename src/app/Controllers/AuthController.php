<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\LoginThrottle;
use App\Core\Router;
use App\Core\Validator;
use App\Models\Usuario;

class AuthController extends BaseController
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function getModel(): Usuario
    {
        return $this->model;
    }

    public function setModel(Usuario $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirLogin();
        }

        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->redirigirLogin('1');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Protección contra fuerza bruta: se comprueba el bloqueo antes de
        // tocar la base de datos para no gastar recursos en intentos inválidos.
        $throttle = new LoginThrottle($username);
        if ($throttle->bloqueado()) {
            $this->redirigirLogin('bloqueado');
        }

        $this->set('username', $username)
            ->set('password', $password);

        // Campos vacíos: se distingue del error de credenciales para poder
        // mostrar en el formulario el mensaje de "completa todos los campos".
        if ($username === '' || $password === '') {
            $this->redirigirLogin('vacio');
        }

        try {
            $username = Validator::username($username, 'usuario');
            $this->set('username', $username);
        } catch (\InvalidArgumentException) {
            $throttle->registrarFallo();
            $this->redirigirLogin('1');
        }

        $usuario = null;
        try {
            $usuario = $this->model->autenticar($username, $password);
        } catch (\PDOException $e) {
            Logger::error($e, 'Login - error de base de datos');
            $this->redirigirLogin('1');
        } catch (\Exception $e) {
            Logger::error($e, 'Login');
            $this->redirigirLogin('1');
        }

        if ($usuario) {
            $throttle->limpiar();
            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $usuario['id'];
            $_SESSION['username']  = $usuario['user_name'];
            $_SESSION['nombre']    = $usuario['nombre'];
            $_SESSION['rol']       = $usuario['rol_nombre'] ?? null;

            $permisos = $this->model->obtenerPermisosPorUsuario((int)$usuario['id']);
            $_SESSION['permisos'] = $permisos;

            $this->setResult([
                'username' => $_SESSION['username'],
                'nombre'   => $_SESSION['nombre'],
                'rol'      => $_SESSION['rol'],
                'permisos' => $_SESSION['permisos'],
            ]);

            $this->redirigir('?pagina=dashboard');
        }

        $throttle->registrarFallo();
        $this->redirigirLogin('1');
    }

    public function logout(): void
    {
        session_regenerate_id(true);
        session_destroy();
        $this->redirigir('?pagina=login');
    }

    /**
     * Redirige a la página de login conservando el código de error.
     */
    private function redirigirLogin(string $error = ''): void
    {
        $destino = '?pagina=login';
        if ($error !== '') {
            $destino .= '&error=' . urlencode($error);
        }
        $this->redirigir($destino);
    }

    /**
     * Envía una redirección HTTP y termina la ejecución.
     */
    private function redirigir(string $destino): void
    {
        header('Location: ' . $destino);
        exit;
    }
}
