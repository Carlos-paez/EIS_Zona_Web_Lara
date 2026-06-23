<!DOCTYPE html>
<!-- Declaración del tipo de documento como HTML5 -->
<html lang="es">
<!-- Inicio del elemento HTML con idioma español -->
<head>
  <!-- Definición del juego de caracteres como UTF-8 (soporta caracteres especiales) -->
  <meta charset="UTF-8">
  <!-- Configuración del viewport para diseño responsive en dispositivos móviles -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Color de tema para la barra de direcciones en navegadores móviles (azul índigo oscuro) -->
  <meta name="theme-color" content="#1a237e">
  <!-- Título de la página mostrado en la pestaña del navegador -->
  <title>Sin Conexión - EIS System</title>
  <!-- Carga la hoja de estilos de los iconos Material Design -->
  <link rel="stylesheet" href="Public/css/material-icons.css">
  <!-- Carga la hoja de estilos del framework Materialize CSS para los componentes visuales -->
  <link rel="stylesheet" href="Public/css/materialize.min.css">
  <!-- Estilos CSS personalizados para la página offline -->
  <style>
    /* Estilo para el fondo de la página: gris claro con flexbox centrado */
    body {
      background: #f5f7fa;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      padding: 1rem;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    /* Estilo para la tarjeta de contenido offline: centrada con ancho máximo */
    .offline-card {
      text-align: center;
      padding: 3rem 2rem;
      max-width: 480px;
      width: 100%;
    }
    /* Estilo para el icono de "sin conexión": grande y en tono azul claro */
    .offline-icon {
      font-size: 5rem;
      color: #7986cb;
      margin-bottom: 1rem;
    }
    /* Estilo para el título: azul oscuro, negrita, con margen inferior reducido */
    h4 { color: #283593; font-weight: 700; margin-bottom: 0.5rem; }
    /* Estilo para el párrafo descriptivo: gris azulado, tamaño ligeramente mayor */
    p { color: #78909c; font-size: 1.1rem; margin-bottom: 2rem; }
    /* Estilo para el botón de reintentar: bordes redondeados y altura definida */
    .retry-btn {
      border-radius: 8px;
      padding: 0 2rem;
      height: 3rem;
      line-height: 3rem;
    }
  </style>
</head>
<body>
  <!-- Tarjeta Materialize con sombra (z-depth-2) que contiene el mensaje offline -->
  <div class="card offline-card z-depth-2">
    <div class="card-content">
      <!-- Icono Material "cloud_off" que representa la falta de conexión a Internet -->
      <div class="offline-icon material-icons">cloud_off</div>
      <!-- Título principal: "Sin Conexión" -->
      <h4>Sin Conexión</h4>
      <!-- Texto descriptivo explicando que no hay conexión a Internet y los recursos ya cargados siguen disponibles -->
      <p>No hay conexión a Internet. Algunas funciones requieren conexión al servidor. Los recursos ya cargados siguen disponibles.</p>
      <!-- Botón que al hacer clic recarga la página para reintentar la conexión -->
      <button class="btn waves-effect waves-light indigo retry-btn" onclick="location.reload()">
        <!-- Icono de refresh (recargar) dentro del botón -->
        <i class="material-icons left">refresh</i>Reintentar
      </button>
    </div>
  </div>
  <!-- Carga la librería jQuery (necesaria para Materialize JS) -->
  <script src="Public/js/jquery-3.7.1.min.js"></script>
  <!-- Carga el JavaScript de Materialize para componentes interactivos -->
  <script src="Public/js/materialize.min.js"></script>
</body>
</html>
