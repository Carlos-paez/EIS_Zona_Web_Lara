<?php
// ============================================================
// CONTROLADOR DE REPORTES Y ESTADÍSTICAS
// ============================================================
// Este controlador maneja la página de reportes que muestra
// KPIs mensuales, un generador de reportes con selección de
// tipo, rango de fechas y formato de salida, y un listado
// de reportes generados recientemente.
//
// Actualmente los datos son estáticos (UI prototype).

namespace App\Controllers;

use App\Core\Controller;

class ReportesController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR REPORTES
    // ============================================
    // Renderiza la vista de reportes dentro del layout.
    // La vista reportes/index.php contiene:
    //   - Tarjetas de métricas mensuales (ventas, productos,
    //     horas cyber, solicitudes)
    //   - Generador de reportes con formulario (tipo, fechas,
    //     formato de salida)
    //   - Listado de reportes generados recientemente
    public function index(): void
    {
        // Renderizar la vista de reportes dentro del layout.
        // La vista se encuentra en src/app/Views/reportes/index.php.
        $this->render('reportes/index');
    }
}
