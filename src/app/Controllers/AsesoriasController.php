<?php
// ============================================================
// CONTROLADOR DE ASESORÍA LEGAL
// ============================================================
// Este controlador maneja la página de asesoría legal que
// permite registrar y validar documentos para asesoría
// jurídica gratuita. Incluye un formulario de registro,
// historial de asesorías y lista de documentos permitidos.
//
// Actualmente la lógica de registro y búsqueda es manejada
// por JavaScript en el lado del cliente (UI prototype).
// El modelo crud_asesorias.php existe para la conexión
// con la base de datos cuando se implemente.

namespace App\Controllers;

use App\Core\Controller;

class AsesoriasController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR ASESORÍA LEGAL
    // ============================================
    // Renderiza la vista de asesorías dentro del layout.
    // La vista asesorias/index.php contiene:
    //   - Banner con título y contador de registros del día
    //   - Formulario de registro (ciudadano, cédula, tipo de
    //     documento, descripción)
    //   - Validación de documentos con sugerencias
    //   - Historial de asesorías registradas con búsqueda
    //   - Lista de documentos permitidos
    public function index(): void
    {
        // Renderizar la vista de asesorías dentro del layout.
        // La vista se encuentra en src/app/Views/asesorias/index.php.
        $this->render('asesorias/index');
    }
}
