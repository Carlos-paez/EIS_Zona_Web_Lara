<?php
// ============================================================
// CONTROLADOR DE GESTIÓN DE ACTIVOS
// ============================================================
// Este controlador maneja la página de activos fijos que
// muestra el inventario de equipos, licencias y herramientas
// con búsqueda, filtro por categoría, tablas agrupadas por
// tipo de activo y un resumen con totales.
//
// Actualmente los datos son estáticos (UI prototype).

namespace App\Controllers;

use App\Core\Controller;

class ActivosController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR ACTIVOS
    // ============================================
    // Renderiza la vista de activos dentro del layout.
    // La vista activos/index.php contiene:
    //   - Barra de herramientas con búsqueda y filtro por categoría
    //   - Botón para nuevo activo
    //   - Tablas agrupadas por tipo: Equipos, Licencias, Herramientas
    //   - Tarjeta de resumen con totales (activos totales,
    //     en mantenimiento, requieren atención)
    public function index(): void
    {
        // Renderizar la vista de activos dentro del layout.
        // La vista se encuentra en src/app/Views/activos/index.php.
        $this->render('activos/index');
    }
}
