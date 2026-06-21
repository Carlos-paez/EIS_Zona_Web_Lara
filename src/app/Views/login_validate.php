<?php
// =============================================================================
// VISTA: LOGIN_VALIDATE (redirección de seguridad)
// =============================================================================
// Propósito: Este archivo nunca debe renderizarse directamente. Si alguien
//            accede a ?pagina=login_validate sin enviar el formulario POST,
//            simplemente redirige al login. La validación real ocurre en
//            AuthController::login() mediante una petición POST.
// =============================================================================

// Redirigir al login si se accede directamente a este archivo
header('Location: ?pagina=login');
exit;
