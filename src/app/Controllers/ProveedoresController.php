<?php
// ============================================================
// CONTROLADOR DE SOLICITUDES A PROVEEDORES
// ============================================================
// Este controlador maneja la página de solicitudes de compra
// realizadas a proveedores, con filtros por estado y búsqueda.
//
// Actualmente los datos son estáticos (UI prototype).
// En producción, los datos vendrían de las tablas 'solicitudes'
// y 'proveedores'.

namespace App\Controllers;

use App\Core\Controller;

class ProveedoresController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR SOLICITUDES
    // ============================================
    // Renderiza la vista de proveedores dentro del layout.
    // La vista proveedores/index.php contiene:
    //   - Barra de herramientas con búsqueda y filtro por estado
    //   - Botón para nueva solicitud
    //   - Tabla de solicitudes con ID, proveedor, fecha,
    //     estado y acciones
    //   - Paginación
    public function index(): void
    {
        // Renderizar la vista de proveedores dentro del layout.
        // La vista se encuentra en src/app/Views/proveedores/index.php.
        $this->render('proveedores/index');
    }
}
