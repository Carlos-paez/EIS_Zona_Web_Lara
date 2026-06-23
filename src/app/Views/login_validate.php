<?php
// =============================================================================
// VISTA: LOGIN_VALIDATE (redirección de seguridad)
// =============================================================================
// Propósito: Este archivo nunca debe renderizarse directamente. Si alguien
//            accede a ?pagina=login_validate sin enviar el formulario POST,
//            simplemente redirige al login. La validación real ocurre en
//            AuthController::login() mediante una petición POST.
// =============================================================================

// Inicio del bloque PHP

// Redirigir al login si se accede directamente a este archivo sin enviar POST
// Se envía una cabecera HTTP Location para redireccionar al navegador
header('Location: ?pagina=login');
// Finaliza la ejecución del script para que no se procese nada más
exit;

// Fin del bloque PHP
