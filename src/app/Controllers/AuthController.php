<?php
// =============================================================================
// CONTROLADOR AuthController (Autenticación)
// =============================================================================
// Propósito: Manejar el inicio de sesión y cierre de sesión de usuarios.
//            Valida las credenciales usando el modelo Usuario y gestiona
//            las variables de sesión.
// =============================================================================
namespace App\Controllers;

use App\Models\Usuario;

class AuthController
{
    // Instancia del modelo Usuario para acceder a la BD
    private Usuario $model;

    // Constructor: inicializa el modelo de usuario
    public function __construct()
    {
        $this->model = new Usuario();
    }

    // Procesa el formulario de inicio de sesión
    public function login(): void
    {
        // Solo acepta peticiones POST (el formulario envía POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?pagina=login'); // Si no es POST, redirige al login
            exit;
        }

        // Obtiene las credenciales del formulario
        $username = $_POST['username'] ?? ''; // Usuario ingresado
        $password = $_POST['password'] ?? ''; // Contraseña ingresada

        // Intenta autenticar al usuario contra la base de datos
        $usuario = $this->model->autenticar($username, $password);

        if ($usuario) {
            // Si la autenticación fue exitosa, guarda los datos en la sesión
            $_SESSION['logged_in'] = true;            // Marca como autenticado
            $_SESSION['user_id']   = $usuario['id'];   // ID del usuario
            $_SESSION['username']  = $usuario['username']; // Nombre de usuario
            $_SESSION['nombre']    = $usuario['nombre'];    // Nombre real
            header('Location: ?pagina=dashboard'); // Redirige al dashboard
            exit;
        }

        // Si las credenciales son incorrectas, redirige con error
        header('Location: ?pagina=login&error=1');
        exit;
    }

    // Cierra la sesión del usuario
    public function logout(): void
    {
        session_destroy();                  // Destruye todas las variables de sesión
        header('Location: ?pagina=login');  // Redirige a la página de login
        exit;
    }
}
