<?php
// ============================================================
// CONTROLADOR DE GESTIÓN DE INVENTARIO
// ============================================================
// Este controlador maneja la página de inventario que muestra
// el listado de productos con su precio, stock, estado y
// acciones disponibles. Incluye búsqueda y filtros.
//
// Actualmente los datos son estáticos (UI prototype).
// En producción, los datos vendrían de la tabla 'productos'
// usando el modelo correspondiente.

namespace App\Controllers;

use App\Core\Controller;

class InventarioController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR EL INVENTARIO
    // ============================================
    // Renderiza la vista de inventario dentro del layout principal.
    // La vista inventario/index.php contiene:
    //   - Barra de herramientas con búsqueda y filtro por estado
    //   - Botón para agregar nuevo producto
    //   - Tabla de listado de productos con ID, nombre, precio,
    //     stock, mínimo, estado y acciones
    //   - Paginación
    public function index(): void
    {
        // Renderizar la vista de inventario dentro del layout.
        // La vista se encuentra en src/app/Views/inventario/index.php.
        $this->render('inventario/index');
    }
}
