<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\Usuario;

class AuthController
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
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

        if (empty($username) || empty($password)) {
            header('Location: ?pagina=login&error=1');
            exit;
        }
        if (mb_strlen($username) < 3) {
            header('Location: ?pagina=login&error=1');
            exit;
        }
        if (mb_strlen($password) < 6) {
            header('Location: ?pagina=login&error=1');
            exit;
        }

        $usuario = $this->model->autenticar($username, $password);

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
