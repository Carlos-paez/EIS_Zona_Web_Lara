<?php
// ============================================================
// CONTROLADOR: CiberController
// Maneja las peticiones HTTP del módulo Cyber Control
// ============================================================

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
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Usuario por defecto (admin)
        $this->usuarioId = 1;
    }

    /**
     * Página principal del módulo Cyber Control
     */
    public function index(): void
    {
        $estaciones = $this->model->obtenerEstaciones();
        $estadisticas = $this->model->obtenerEstadisticas();

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

        // Obtener clientes y tarifas
        $clientes = $this->model->obtenerClientes();
        $tarifas = $this->model->obtenerTarifas();

        // Funciones auxiliares
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

        $pageTitle = 'Control de Cybercafé';
        $headerExtra = '<span class="chip green white-text">' . $countDisponibles . ' Disponibles</span>
                        <span class="chip orange white-text">' . $countOcupadas . ' Ocupadas</span>';
        
        $viewData = [
            'estaciones' => $estaciones,
            'estadisticas' => $estadisticas,
            'countDisponibles' => $countDisponibles,
            'countOcupadas' => $countOcupadas,
            'countMantenimiento' => $countMantenimiento,
            'totalEstaciones' => $totalEstaciones,
            'usarZonas' => false,
            'estacionesGaming' => [],
            'estacionesOficina' => [],
            'estacionesPremium' => [],
            'getEstadoClase' => $getEstadoClase,
            'getEstadoTexto' => $getEstadoTexto,
            'getEstadoIcono' => $getEstadoIcono,
            'clientes' => $clientes,
            'tarifas' => $tarifas
        ];
        
        extract($viewData);
        
        $contentView = __DIR__ . '/../Views/ciberControl.php';
        require_once __DIR__ . '/../template/layout.php';
    }

    /**
     * API: Iniciar sesión
     */
    public function iniciarSesion(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $activoId = isset($_POST['activo_id']) ? (int)$_POST['activo_id'] : 0;
        $clienteId = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
        $tarifaId = isset($_POST['tarifa_id']) ? (int)$_POST['tarifa_id'] : 0;
        $tiempo = isset($_POST['tiempo']) ? trim($_POST['tiempo']) : '';

        if ($activoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de PC inválido']);
            return;
        }

        if ($clienteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar un cliente']);
            return;
        }

        if ($tarifaId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar una tarifa']);
            return;
        }

        if (empty($tiempo) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $tiempo) || $tiempo === '00:00:00') {
            echo json_encode(['success' => false, 'message' => 'Tiempo inválido. Usa HH:MM:SS']);
            return;
        }

        $resultado = $this->model->iniciarSesion($activoId, $clienteId, $tarifaId, $tiempo);
        echo json_encode($resultado);
    }

    /**
     * API: Finalizar sesión
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
        echo json_encode($resultado);
    }

    /**
     * API: Obtener historial
     */
    public function obtenerHistorial(): void
    {
        header('Content-Type: application/json');

        $activoId = isset($_GET['activo_id']) ? (int)$_GET['activo_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

        if ($activoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de PC inválido']);
            return;
        }

        $historial = $this->model->obtenerHistorialEstacion($activoId, $limit);
        echo json_encode(['success' => true, 'data' => $historial]);
    }

    /**
     * API: Obtener estadísticas
     */
    public function obtenerEstadisticas(): void
    {
        header('Content-Type: application/json');

        $estadisticas = $this->model->obtenerEstadisticas();
        echo json_encode(['success' => true, 'data' => $estadisticas]);
    }
}