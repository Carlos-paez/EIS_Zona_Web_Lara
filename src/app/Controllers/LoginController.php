<?php
// ============================================================
// CONTROLADOR DE INICIO DE SESIÓN (LOGIN)
// ============================================================
// Este controlador maneja dos acciones:
//   1. index(): Muestra el formulario de inicio de sesión
//   2. validate(): Procesa las credenciales enviadas por POST
//
// La lógica de autenticación actual usa credenciales hardcodeadas
// (admin/1234). En producción, debería usar la base de datos
// consultando la tabla 'usuarios' con password_verify().
//
// Este controlador extiende Controller para heredar los métodos
// render() y renderPublic().

namespace App\Controllers;

use App\Core\Controller;

class LoginController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR FORMULARIO DE LOGIN
    // ============================================
    // Renderiza la vista pública del formulario de inicio de
    // sesión. Es una página pública (no requiere autenticación)
    // con su propia estructura HTML completa (sin layout).
    //
    // La vista auth/login.php contiene:
    //   - Formulario con campos de usuario y contraseña
    //   - Mensaje de error si $_GET['error'] está presente
    //   - Botones de autenticación social (placeholders)
    //   - Selector de tema oscuro/claro
    public function index(): void
    {
        // Llamar a renderPublic() para mostrar la vista sin layout.
        // La vista se encuentra en src/app/Views/auth/login.php.
        $this->renderPublic('auth/login');
    }

    // ============================================
    // MÉTODO VALIDATE — PROCESAR CREDENCIALES
    // ============================================
    // Este método recibe los datos del formulario de login
    // enviados por POST. Verifica las credenciales y:
    //   - Si son correctas: inicia sesión y redirige al dashboard
    //   - Si son incorrectas: redirige al login con mensaje de error
    //   - Si no es POST: redirige al login
    //
    // Actualmente usa credenciales fijas (admin/1234).
    // Para usar la base de datos, se debería llamar a:
    //   require_once __DIR__.'/../Models/crud_users.php';
    //   $usuario = autenticarUsuario($pdo, $username, $password);
    public function validate(): void
    {
        // ============================================
        // 1. VERIFICAR QUE LA SOLICITUD SEA POST
        // ============================================
        // $_SERVER["REQUEST_METHOD"] contiene el método HTTP usado
        // (GET, POST, PUT, DELETE, etc.). Solo procesamos POST
        // porque viene del formulario de login.
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // ============================================
            // 2. OBTENER CREDENCIALES DEL FORMULARIO
            // ============================================
            // $_POST["username"] contiene el valor del campo
            // <input name="username"> del formulario.
            // El operador ?? asigna '' si el campo no existe.
            $username = $_POST["username"] ?? '';
            $password = $_POST["password"] ?? '';

            // ============================================
            // 3. DEFINIR CREDENCIALES VÁLIDAS (HARDCODEADAS)
            // ============================================
            // En un sistema real, estas credenciales se buscarían
            // en la base de datos usando la función autenticarUsuario()
            // del modelo crud_users.php, que usa password_verify()
            // para comparar con el hash almacenado.
            $valid_username = "admin";  // Nombre de usuario válido
            $valid_password = "1234";   // Contraseña válida

            // ============================================
            // 4. COMPARAR CREDENCIALES
            // ============================================
            // Verificar que ambos valores coincidan exactamente.
            // Con base de datos sería:
            //   $usuario = autenticarUsuario($pdo, $username, $password);
            //   if ($usuario) { ... }
            if ($username === $valid_username && $password === $valid_password) {

                // ============================================
                // 5. CREDENCIALES CORRECTAS — INICIAR SESIÓN
                // ============================================
                // Establecer variables de sesión que indican que
                // el usuario está autenticado. Estas variables se
                // almacenan en el servidor y persisten entre
                // solicitudes gracias a session_start().
                $_SESSION['logged_in'] = true;     // Marca de autenticación
                $_SESSION['username'] = $username;  // Nombre de usuario

                // Redirigir al panel de control (dashboard)
                header("Location: ?pagina=dashboard");
                exit; // Detener ejecución

            } else {
                // ============================================
                // 6. CREDENCIALES INCORRECTAS
                // ============================================
                // Redirigir al login con el parámetro 'error' para
                // que la vista muestre un mensaje de error.
                header("Location: ?pagina=login&error=1");
                exit;
            }
        }

        // ============================================
        // 7. ACCESO DIRECTO SIN POST
        // ============================================
        // Si alguien accede a ?pagina=login_validate directamente
        // sin enviar el formulario (GET en lugar de POST),
        // redirigir al login.
        header("Location: ?pagina=login");
        exit;
    }
}
