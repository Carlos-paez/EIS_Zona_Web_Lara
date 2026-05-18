<?php
// ============================================================
// CONTROLADOR DEL MENÚ PRINCIPAL
// ============================================================
// Este controlador maneja una página de menú alternativa
// que muestra enlaces a los módulos principales en formato
// de tarjeta con diseño tipo dashboard.
//
// NOTA: Esta vista tiene su propia estructura HTML completa
// (con <html>, <head>, <body>) pero se renderiza DENTRO del
// layout principal debido a cómo está configurada la ruta
// en el Router (no es pública, usa render()). Esto puede
// causar anidamiento de etiquetas HTML.

namespace App\Controllers;

use App\Core\Controller;

class MenuController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR EL MENÚ PRINCIPAL
    // ============================================
    // Renderiza la vista del menú dentro del layout.
    // La vista menu/index.php contiene:
    //   - Tarjeta con logotipo y título "Menú Principal"
    //   - Lista de enlaces a todos los módulos del sistema
    //   - Información del usuario actual
    //   - Selector de tema oscuro/claro
    public function index(): void
    {
        // Renderizar la vista del menú dentro del layout.
        // La vista se encuentra en src/app/Views/menu/index.php.
        $this->render('menu/index');
    }
}
