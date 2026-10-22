<?php
// Vista de acceso denegado (HTTP 403). Se muestra dentro del layout
// cuando el usuario autenticado no tiene permiso para acceder a un módulo.
?>
<div class="row" style="padding-top:3rem;">
    <div class="col s12 m8 offset-m2 l6 offset-l3 center-align">
        <i class="material-icons" style="font-size:6rem;color:#e53935;">block</i>
        <h3 class="red-text text-darken-2">Acceso denegado</h3>
        <p class="grey-text text-darken-1" style="font-size:1.1rem;">
            No tienes permisos para acceder a este módulo.<br>
            Si necesitas acceso, solicita al administrador que te asigne los permisos correspondientes.
        </p>
        <p class="grey-text text-darken-1">
            Usuario: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
            <?php if (!empty($_SESSION['rol'])): ?>
            &nbsp;|&nbsp; Rol: <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>
            <?php endif; ?>
        </p>
        <br>
        <a href="?pagina=dashboard" class="btn-large indigo waves-effect waves-light">
            <i class="material-icons left">home</i>Volver al dashboard
        </a>
        <a href="?pagina=manual_usuario" class="btn-large white black-text waves-effect">
            <i class="material-icons left">menu_book</i>Manual de usuario
        </a>
    </div>
</div>
