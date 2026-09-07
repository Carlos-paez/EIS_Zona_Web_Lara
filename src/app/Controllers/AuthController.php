<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\Validator;
use App\Models\Usuario;

class AuthController
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

    public function setModel(Usuario $model): void
    {
        $this->model = $model;
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?pagina=login');
            exit;
        }

        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            header('Location: ?pagina=login&error=1');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Campos vacíos: se distingue del error de credenciales para poder
        // mostrar en el formulario el mensaje de "completa todos los campos".
        if ($username === '' || $password === '') {
            header('Location: ?pagina=login&error=vacio');
            exit;
        }

        try {
            $username = Validator::username($username, 'usuario');
        } catch (\InvalidArgumentException) {
            header('Location: ?pagina=login&error=1');
            exit;
        }

        if (mb_strlen($username) < 3) {
            header('Location: ?pagina=login&error=1');
            exit;
        }

        $usuario = null;
        try {
            $usuario = $this->model->autenticar($username, $password);
        } catch (\PDOException $e) {
            Logger::error($e, 'Login - error de base de datos');
            header('Location: ?pagina=login&error=1');
            exit;
        } catch (\Exception $e) {
            Logger::error($e, 'Login');
            header('Location: ?pagina=login&error=1');
            exit;
        }

        if ($usuario) {
            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $usuario['id'];
            $_SESSION['username']  = $usuario['user_name'];
            $_SESSION['nombre']    = $usuario['nombre'];

            header('Location: ?pagina=dashboard');
            exit;
        }

        header('Location: ?pagina=login&error=1');
        exit;
    }

    public function logout(): void
    {
        session_regenerate_id(true);
        session_destroy();
        header('Location: ?pagina=login');
        exit;
    }
}
