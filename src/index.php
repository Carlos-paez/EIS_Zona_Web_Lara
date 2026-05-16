<?php
// Punto de entrada único de la aplicación (Front Controller).
// Todas las solicitudes HTTP pasan por aquí gracias a las reglas de reescritura de Apache (.htaccess).

    require_once __DIR__.'/app/core/router.php'; // Incluye el router, que maneja la lógica de navegación y autenticación