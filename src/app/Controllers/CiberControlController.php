<?php
// ============================================================
// CONTROLADOR DE CONTROL DE CYBERCAFÉ
// ============================================================
// Este controlador maneja la página de control de cybercafé
// que muestra un grid de estaciones con su estado (disponible,
// ocupada, mantenimiento), filtros y métricas.
//
// A diferencia de otros controladores, este SÍ prepara datos
// en el servidor (los datos de las estaciones y los conteos)
// y los pasa a la vista. Los datos actuales son estáticos
// (UI prototype), pero la estructura está lista para
// conectarse a la base de datos.

namespace App\Controllers;

use App\Core\Controller;

class CiberControlController extends Controller
{
    // ============================================
    // MÉTODO INDEX — MOSTRAR CONTROL DE CYBER
    // ============================================
    // Prepara los datos de las estaciones de cybercafé y
    // renderiza la vista dentro del layout principal.
    public function index(): void
    {
        // ============================================
        // 1. DATOS DE LAS ESTACIONES (ESTÁTICOS)
        // ============================================
        // Array multidimensional que define las zonas y sus
        // estaciones. Cada estación tiene:
        //   - num: número identificador de la estación
        //   - status: 'disponible', 'ocupada' o 'mantenimiento'
        //   - icono: ícono de Material Icons según el estado
        //   - desc: descripción de la estación o tiempo restante
        //   - precio: (opcional) monto acumulado si está ocupada
        $zonas = [
            'Zona A' => [
                ['num' => 1,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
                ['num' => 2,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '45 min restantes',  'precio' => 2.50],
                ['num' => 3,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
                ['num' => 4,  'status' => 'mantenimiento', 'icono' => 'build',       'desc' => 'Teclado dañado'],
            ],
            'Zona B' => [
                ['num' => 5,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '1h 20 min restantes', 'precio' => 4.50],
                ['num' => 6,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
                ['num' => 7,  'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '30 min restantes',   'precio' => 1.50],
            ],
            'Zona C' => [
                ['num' => 8,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Estándar'],
                ['num' => 9,  'status' => 'disponible',   'icono' => 'check_circle', 'desc' => 'PC Gaming'],
                ['num' => 10, 'status' => 'ocupada',      'icono' => 'timelapse',   'desc' => '2h restantes',       'precio' => 6.00],
            ],
        ];

        // ============================================
        // 2. CÁLCULO DE MÉTRICAS
        // ============================================
        // array_merge(...array_values($zonas)) aplana el array
        // multidimensional en un solo array de estaciones.
        // array_values() obtiene los valores de cada zona.
        // El operador ... (spread) desempaqueta los arrays.
        $todasEstaciones = array_merge(...array_values($zonas));

        // Contar estaciones por estado usando array_filter()
        // con una función flecha (fn) que devuelve true si
        // el estado coincide.
        $countDisponibles   = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'disponible'));
        $countOcupadas      = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'ocupada'));
        $countMantenimiento = count(array_filter($todasEstaciones, fn($e) => $e['status'] === 'mantenimiento'));
        $totalEstaciones    = count($todasEstaciones);

        // ============================================
        // 3. ETIQUETAS DE ESTADO
        // ============================================
        // Mapeo de estados internos (inglés) a etiquetas
        // legibles en español para mostrar en la UI.
        $statusLabels = [
            'disponible'   => 'Disponible',
            'ocupada'      => 'Ocupada',
            'mantenimiento' => 'Mantenimiento',
        ];

        // ============================================
        // 4. RENDERIZAR LA VISTA
        // ============================================
        // Pasar todos los datos calculados a la vista usando
        // compact(). compact() crea un array asociativo donde
        // las claves son los nombres de las variables.
        // Ej: compact('zonas') → ['zonas' => $zonas]
        // La vista recibe estos datos como variables individuales
        // gracias a extract() en el método render().
        $this->render('ciber-control/index', compact(
            'zonas', 'countDisponibles', 'countOcupadas',
            'countMantenimiento', 'totalEstaciones', 'statusLabels'
        ));
    }
}
