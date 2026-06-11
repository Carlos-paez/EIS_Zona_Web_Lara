<?php
namespace App\Controllers;

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

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $usuario = $this->model->autenticar($username, $password);

        if ($usuario) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $usuario['id'];
            $_SESSION['username']  = $usuario['username'];
            $_SESSION['nombre']    = $usuario['nombre'];
            header('Location: ?pagina=dashboard');
            exit;
        }

        header('Location: ?pagina=login&error=1');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ?pagina=login');
        exit;
    }
}
