<?php
// ============================================================
// CONTROLADOR DE PUNTO DE VENTA (POS)
// ============================================================
// Este controlador maneja la interfaz del punto de venta
// que muestra un catálogo de productos seleccionables que
// se agregan a un carrito de compras modal.
//
// La lógica del carrito (agregar, quitar, calcular total,
// procesar venta) es manejada completamente por JavaScript
// en el lado del cliente (app.js). Los productos y precios
// son estáticos (UI prototype).

namespace App\Controllers;

use App\Core\Controller;

class VentasController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR EL PUNTO DE VENTA
    // ============================================
    // Renderiza la vista del POS dentro del layout principal.
    // La vista ventas/index.php contiene:
    //   - Encabezado con el total del carrito
    //   - Botón para abrir el modal del carrito
    //   - Catálogo de productos en formato grid
    //   - Modal del carrito con lista de productos, total
    //     y botones para vaciar o procesar la venta
    public function index(): void
    {
        // Renderizar la vista de ventas dentro del layout.
        // La vista se encuentra en src/app/Views/ventas/index.php.
        $this->render('ventas/index');
    }
}
