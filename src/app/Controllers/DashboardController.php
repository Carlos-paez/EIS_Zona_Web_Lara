<?php
// ============================================================
// CONTROLADOR DEL PANEL DE CONTROL (DASHBOARD)
// ============================================================
// Este controlador maneja la página principal del sistema
// que se muestra después del inicio de sesión.
// Muestra métricas clave del negocio: ventas del día, stock
// crítico, sesiones de cybercafé, solicitudes pendientes,
// horas pico, productos sin stock y actividad reciente.
//
// Actualmente todos los datos son estáticos (UI prototype).
// En producción, los datos vendrían de los Models consultando
// la base de datos.

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR EL PANEL DE CONTROL
    // ============================================
    // Renderiza la vista del dashboard dentro del layout principal.
    // La vista dashboard/index.php contiene:
    //   - Banner de bienvenida
    //   - Tarjetas de métricas (KPI): ventas hoy, stock crítico,
    //     sesiones cyber, solicitudes pendientes
    //   - Tabla de horas pico de ventas
    //   - Tabla de productos sin stock
    //   - Lista de actividad reciente
    //
    // No recibe datos del controlador porque actualmente usa
    // datos estáticos directamente en la vista.
    public function index(): void
    {
        // Renderizar la vista del dashboard dentro del layout.
        // La vista se encuentra en src/app/Views/dashboard/index.php.
        // El segundo parámetro (array vacío) indica que no se
        // pasan datos adicionales a la vista.
        $this->render('dashboard/index');
    }
}
