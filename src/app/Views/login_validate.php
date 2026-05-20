<?php
// Validador de credenciales de inicio de sesión
// La sesión ya fue iniciada por router.php (session_start())
// Este archivo solo se ejecuta cuando se envía el formulario de login vía POST

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica que la solicitud sea POST (envío de formulario)

    $username = $_POST["username"] ?? ''; // Obtiene el nombre de usuario del formulario, o cadena vacía si no existe
    $password = $_POST["password"] ?? ''; // Obtiene la contraseña del formulario, o cadena vacía si no existe

    $valid_username = "admin"; // Usuario válido hardcodeado (debería estar en base de datos)
    $valid_password = "1234";  // Contraseña válida hardcodeada (debería estar hasheada)

    if ($username === $valid_username && $password === $valid_password) {
        // Credenciales correctas: establece variables de sesión y redirige al dashboard
        $_SESSION['logged_in'] = true;    // Marca al usuario como autenticado
        $_SESSION['username'] = $username; // Guarda el nombre de usuario en sesión
        header("Location: ?pagina=dashboard"); // Redirige al panel de control
        exit; // Detiene la ejecución del script
    } else {
        // Credenciales incorrectas: redirige al login con mensaje de error
        header("Location: ?pagina=login&error=1"); // Parámetro 'error' para mostrar alerta
        exit;
    }
}

// Si alguien accede directamente a este archivo sin enviar POST, redirige al login
header("Location: ?pagina=login");
exit;

