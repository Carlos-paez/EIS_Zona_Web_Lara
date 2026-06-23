<?php
// =============================================================================
// CONTROLADOR AuthController (Autenticación)
// =============================================================================
// Propósito: Manejar el inicio de sesión y cierre de sesión de usuarios.
//            Valida las credenciales usando el modelo Usuario y gestiona
//            las variables de sesión.
// =============================================================================

// Declara el espacio de nombres al que pertenece esta clase, siguiendo la estructura PSR-4
namespace App\Controllers;

// Importa el modelo Usuario para poder autenticar contra la base de datos
use App\Models\Usuario;

/**
 * Controlador de autenticación de usuarios
 * 
 * Procesa el inicio de sesión (login) y el cierre de sesión (logout)
 * de los usuarios del sistema. Verifica las credenciales contra la base
 * de datos y gestiona las variables de sesión de PHP.
 */
class AuthController
{
    /**
     * Instancia del modelo Usuario
     * 
     * Almacena el objeto del modelo que proporciona los métodos
     * para consultar y autenticar usuarios en la base de datos.
     */
    private Usuario $model;

    /**
     * Constructor de la clase AuthController
     * 
     * Inicializa la propiedad $model creando una nueva instancia
     * del modelo Usuario, que será usada para acceder a los datos
     * de usuarios en la base de datos.
     */
    public function __construct()
    {
        // Crea una nueva instancia del modelo Usuario y la asigna a la propiedad $model
        $this->model = new Usuario();
    }

    /**
     * Procesa el formulario de inicio de sesión
     * 
     * Valida que la petición sea de tipo POST, obtiene las credenciales
     * enviadas desde el formulario, las envía al modelo para autenticar
     * al usuario y, si es exitoso, establece las variables de sesión.
     * En caso de error, redirige de vuelta al login con un parámetro de error.
     *
     * @return void No retorna ningún valor, siempre redirige con header()
     */
    public function login(): void
    {
        // Verifica que el método de la petición HTTP sea POST (el formulario de login envía POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Si no es una petición POST, redirige al usuario a la página de login
            header('Location: ?pagina=login');
            // Detiene la ejecución del script para asegurar que la redirección se complete
            exit;
        }

        // Obtiene el nombre de usuario enviado desde el formulario, o cadena vacía si no existe
        $username = $_POST['username'] ?? '';
        // Obtiene la contraseña enviada desde el formulario, o cadena vacía si no existe
        $password = $_POST['password'] ?? '';

        // Llama al método autenticar del modelo Usuario, que verifica las credenciales en la BD
        // Devuelve un arreglo con los datos del usuario si son válidas, o false si no
        $usuario = $this->model->autenticar($username, $password);

        // Evalúa si la autenticación fue exitosa (devuelve un arreglo con datos del usuario)
        if ($usuario) {
            // Establece la variable de sesión 'logged_in' como true para indicar sesión activa
            $_SESSION['logged_in'] = true;
            // Guarda el ID del usuario autenticado en la sesión para referencias futuras
            $_SESSION['user_id']   = $usuario['id'];
            // Guarda el nombre de usuario (user_name) en la sesión
            $_SESSION['username']  = $usuario['user_name'];
            // Guarda el nombre completo del usuario en la sesión
            $_SESSION['nombre']    = $usuario['nombre'];
            // Redirige al dashboard (página principal del sistema) después del login exitoso
            header('Location: ?pagina=dashboard');
            // Detiene la ejecución para completar la redirección
            exit;
        }

        // Si las credenciales son incorrectas, redirige al login con un parámetro de error
        header('Location: ?pagina=login&error=1');
        // Detiene la ejecución para completar la redirección
        exit;
    }

    /**
     * Cierra la sesión del usuario actual
     * 
     * Destruye todas las variables de sesión y redirige
     * al usuario a la página de inicio de sesión.
     *
     * @return void No retorna ningún valor, siempre redirige con header()
     */
    public function logout(): void
    {
        // Destruye completamente la sesión actual, eliminando todas las variables de sesión
        session_destroy();
        // Redirige al usuario a la página de login después de cerrar sesión
        header('Location: ?pagina=login');
        // Detiene la ejecución para completar la redirección
        exit;
    }
}
