<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1a237e">
  <title>Sin Conexión - EIS System</title>
  <link rel="stylesheet" href="Public/css/material-icons.css">
  <link rel="stylesheet" href="Public/css/materialize.min.css">
  <style>
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
    .offline-card {
      text-align: center;
      padding: 3rem 2rem;
      max-width: 480px;
      width: 100%;
    }
    .offline-icon {
      font-size: 5rem;
      color: #7986cb;
      margin-bottom: 1rem;
    }
    h4 { color: #283593; font-weight: 700; margin-bottom: 0.5rem; }
    p { color: #78909c; font-size: 1.1rem; margin-bottom: 2rem; }
    .retry-btn {
      border-radius: 8px;
      padding: 0 2rem;
      height: 3rem;
      line-height: 3rem;
    }
  </style>
</head>
<body>
  <div class="card offline-card z-depth-2">
    <div class="card-content">
      <div class="offline-icon material-icons">cloud_off</div>
      <h4>Sin Conexión</h4>
      <p>No hay conexión a Internet. Algunas funciones requieren conexión al servidor. Los recursos ya cargados siguen disponibles.</p>
      <button class="btn waves-effect waves-light indigo retry-btn" onclick="location.reload()">
        <i class="material-icons left">refresh</i>Reintentar
      </button>
    </div>
  </div>
  <script src="Public/js/jquery-3.7.1.min.js"></script>
  <script src="Public/js/materialize.min.js"></script>
</body>
</html>
