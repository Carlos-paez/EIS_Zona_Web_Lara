<?php
// src/app/Controllers/CiberController.php

require_once __DIR__ . '/../Models/CiberModel.php';

class CiberController
{
    /** @var CiberModel */
    private $model;

    /** @var int|null ID del usuario logueado */
    private $usuarioId;

    public function __construct()
    {
        $this->model = new CiberModel();
        
        // Obtener el ID del usuario de la sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Buscar el usuario admin en la base de datos
        try {
            global $pdo;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = 'admin' LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch();
                $this->usuarioId = $admin ? $admin['id'] : 1;
            } else {
                $this->usuarioId = 1;
            }
        } catch (Exception $e) {
            $this->usuarioId = 1;
        }
    }

    /**
     * Página principal del módulo Cyber Control
     */
    public function index(): void
    {
        // Obtener datos del modelo
        $estaciones = $this->model->obtenerEstaciones();
        $estadisticas = $this->model->obtenerEstadisticas();

        // Calcular contadores
        $countDisponibles = 0;
        $countOcupadas = 0;
        $countMantenimiento = 0;

        foreach ($estaciones as $e) {
            switch ($e['estado']) {
                case 'Disponible':
                    $countDisponibles++;
                    break;
                case 'Ocupada':
                    $countOcupadas++;
                    break;
                case 'Mantenimiento':
                    $countMantenimiento++;
                    break;
            }
        }
        $totalEstaciones = count($estaciones);

        // Agrupar estaciones por zona/tipo
        $estacionesGaming = array_filter($estaciones, function($e) {
            return strpos($e['especificaciones'] ?? '', 'Gaming') !== false || 
                   ($e['tarifa_nombre'] ?? '') === 'Gaming';
        });

        $estacionesOficina = array_filter($estaciones, function($e) {
            return strpos($e['especificaciones'] ?? '', 'Estándar') !== false || 
                   ($e['tarifa_nombre'] ?? '') === 'Oficina';
        });

        $estacionesPremium = array_filter($estaciones, function($e) {
            return ($e['tarifa_nombre'] ?? '') === 'Premium';
        });

        $usarZonas = count($estacionesGaming) > 0 || 
                     count($estacionesOficina) > 0 || 
                     count($estacionesPremium) > 0;

        // Funciones auxiliares para estados
        $getEstadoClase = function($estado) {
            switch ($estado) {
                case 'Disponible': return 'disponible';
                case 'Ocupada': return 'ocupada';
                case 'Mantenimiento': return 'mantenimiento';
                default: return 'disponible';
            }
        };

        $getEstadoTexto = function($estado) {
            switch ($estado) {
                case 'Disponible': return 'Disponible';
                case 'Ocupada': return 'Ocupada';
                case 'Mantenimiento': return 'Mantenimiento';
                default: return $estado;
            }
        };

        $getEstadoIcono = function($estado) {
            switch ($estado) {
                case 'Disponible': return 'check_circle';
                case 'Ocupada': return 'timelapse';
                case 'Mantenimiento': return 'build';
                default: return 'help';
            }
        };

        // ============================================================
        // PASAR VARIABLES AL LAYOUT Y A LA VISTA
        // ============================================================
        $pageTitle = 'Control de Cybercafé';
        $headerExtra = '<span class="chip green white-text">' . $countDisponibles . ' Disponibles</span><span class="chip orange white-text">' . $countOcupadas . ' Ocupadas</span>';
        
        // Guardar todas las variables en un array para pasarlas al layout
        $viewData = [
            'estaciones' => $estaciones,
            'estadisticas' => $estadisticas,
            'countDisponibles' => $countDisponibles,
            'countOcupadas' => $countOcupadas,
            'countMantenimiento' => $countMantenimiento,
            'totalEstaciones' => $totalEstaciones,
            'usarZonas' => $usarZonas,
            'estacionesGaming' => $estacionesGaming,
            'estacionesOficina' => $estacionesOficina,
            'estacionesPremium' => $estacionesPremium,
            'getEstadoClase' => $getEstadoClase,
            'getEstadoTexto' => $getEstadoTexto,
            'getEstadoIcono' => $getEstadoIcono
        ];
        
        // Extraer variables para que estén disponibles en la vista
        extract($viewData);
        
        // Incluir el layout (que a su vez incluirá la vista)
        $contentView = __DIR__ . '/../Views/ciberControl.php';
        require_once __DIR__ . '/../template/layout.php';
    }

    /**
     * API: Iniciar sesión en una estación
     */
    public function iniciarSesion(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $estacionId = isset($_POST['estacion_id']) ? (int)$_POST['estacion_id'] : 0;
        $clienteNombre = isset($_POST['cliente_nombre']) ? trim($_POST['cliente_nombre']) : '';

        if ($estacionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de estación inválido']);
            return;
        }

        if (empty($clienteNombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre del cliente es obligatorio']);
            return;
        }

        $resultado = $this->model->iniciarSesion($estacionId, $this->usuarioId, $clienteNombre);

        if ($resultado['success']) {
            // Obtener datos actualizados de la estación
            $estaciones = $this->model->obtenerEstaciones();
            $estacionActualizada = null;
            foreach ($estaciones as $e) {
                if ($e['id'] == $estacionId) {
                    $estacionActualizada = $e;
                    break;
                }
            }
            $resultado['data']['estacion'] = $estacionActualizada;
        }

        echo json_encode($resultado);
    }

    /**
     * API: Finalizar sesión en una estación
     */
    public function finalizarSesion(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $sesionId = isset($_POST['sesion_id']) ? (int)$_POST['sesion_id'] : 0;

        if ($sesionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de sesión inválido']);
            return;
        }

        $resultado = $this->model->finalizarSesion($sesionId);

        if ($resultado['success']) {
            $estacionId = $resultado['data']['estacion_id'];
            $estaciones = $this->model->obtenerEstaciones();
            $estacionActualizada = null;
            foreach ($estaciones as $e) {
                if ($e['id'] == $estacionId) {
                    $estacionActualizada = $e;
                    break;
                }
            }
            $resultado['data']['estacion'] = $estacionActualizada;
        }

        echo json_encode($resultado);
    }

    /**
     * API: Obtener historial de una estación
     */
    public function obtenerHistorial(): void
    {
        header('Content-Type: application/json');

        $estacionId = isset($_GET['estacion_id']) ? (int)$_GET['estacion_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        if ($estacionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de estación inválido']);
            return;
        }

        $historial = $this->model->obtenerHistorialEstacion($estacionId, $limit);

        echo json_encode(['success' => true, 'data' => $historial]);
    }

    /**
     * API: Obtener estadísticas actualizadas
     */
    public function obtenerEstadisticas(): void
    {
        header('Content-Type: application/json');

        $estadisticas = $this->model->obtenerEstadisticas();
        
        $estaciones = $this->model->obtenerEstaciones();
        $countDisponibles = 0;
        $countOcupadas = 0;
        $countMantenimiento = 0;

        foreach ($estaciones as $e) {
            switch ($e['estado']) {
                case 'Disponible':
                    $countDisponibles++;
                    break;
                case 'Ocupada':
                    $countOcupadas++;
                    break;
                case 'Mantenimiento':
                    $countMantenimiento++;
                    break;
            }
        }

        $estadisticas['disponibles'] = $countDisponibles;
        $estadisticas['ocupadas'] = $countOcupadas;
        $estadisticas['mantenimiento'] = $countMantenimiento;
        $estadisticas['total'] = count($estaciones);

        echo json_encode(['success' => true, 'data' => $estadisticas]);
    }
}