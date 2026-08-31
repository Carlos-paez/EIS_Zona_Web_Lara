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
        $tiposActivo = [];
        if (method_exists($this->model, 'obtenerTiposActivo')) {
            $tiposActivo = $this->model->obtenerTiposActivo();
        }

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
            'tarifas' => $tarifas,
            'tiposActivo' => $tiposActivo
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
        $clienteNombre = isset($_POST['cliente_nombre']) ? trim($_POST['cliente_nombre']) : '';
        $tarifaId = isset($_POST['tarifa_id']) ? (int)$_POST['tarifa_id'] : 0;
        $tiempo = isset($_POST['tiempo']) ? trim($_POST['tiempo']) : '';

        if ($activoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de PC inválido']);
            return;
        }

        if (empty($clienteNombre)) {
            echo json_encode(['success' => false, 'message' => 'Debes escribir el nombre del cliente']);
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

        $clienteId = $this->model->buscarClientePorNombre($clienteNombre);
        if ($clienteId <= 0) {
            $clienteId = $this->model->crearClientePorNombre($clienteNombre);
            if ($clienteId <= 0) {
                echo json_encode(['success' => false, 'message' => 'No se pudo registrar el cliente']);
                return;
            }
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
/**
 * API: Obtener una PC para editar
 */
public function obtenerPC(): void
{
    header('Content-Type: application/json');
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    $pc = $this->model->obtenerPC($id);
    
    if ($pc) {
        echo json_encode(['success' => true, 'data' => $pc]);
    } else {
        echo json_encode(['success' => false, 'message' => 'PC no encontrada']);
    }
}

/**
 * API: Crear nueva PC
 */
public function crearPC(): void
{
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $marca = isset($_POST['marca']) ? trim($_POST['marca']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $tipo_activo_id = isset($_POST['tipo_activo_id']) ? (int)$_POST['tipo_activo_id'] : 0;
    $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 1;
    $is_ciber = 1; // Siempre es 1 para PCs de cyber
    
    // Validaciones
    if (empty($marca)) {
        echo json_encode(['success' => false, 'message' => 'La marca es obligatoria']);
        return;
    }
    
    if (empty($descripcion)) {
        echo json_encode(['success' => false, 'message' => 'La descripción es obligatoria']);
        return;
    }
    
    if ($tipo_activo_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Debes seleccionar un tipo de PC']);
        return;
    }
    
    $resultado = $this->model->crearPC([
        'marca' => $marca,
        'descripcion' => $descripcion,
        'tipo_activo_id' => $tipo_activo_id,
        'activa' => $activa,
        'is_ciber' => $is_ciber
    ]);
    
    if ($resultado['success']) {
        // Obtener la PC recién creada para actualizar la UI
        $pc = $this->model->obtenerPC($resultado['id']);
        echo json_encode([
            'success' => true,
            'message' => 'PC creada exitosamente',
            'data' => $pc
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
}

/**
 * API: Actualizar PC
 */
public function actualizarPC(): void
{
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $marca = isset($_POST['marca']) ? trim($_POST['marca']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $tipo_activo_id = isset($_POST['tipo_activo_id']) ? (int)$_POST['tipo_activo_id'] : 0;
    $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 1;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    if (empty($marca)) {
        echo json_encode(['success' => false, 'message' => 'La marca es obligatoria']);
        return;
    }
    
    if (empty($descripcion)) {
        echo json_encode(['success' => false, 'message' => 'La descripción es obligatoria']);
        return;
    }
    
    if ($tipo_activo_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Debes seleccionar un tipo de PC']);
        return;
    }
    
    $resultado = $this->model->actualizarPC($id, [
        'marca' => $marca,
        'descripcion' => $descripcion,
        'tipo_activo_id' => $tipo_activo_id,
        'activa' => $activa
    ]);
    
    if ($resultado['success']) {
        // Obtener la PC actualizada
        $pc = $this->model->obtenerPC($id);
        echo json_encode([
            'success' => true,
            'message' => 'PC actualizada exitosamente',
            'data' => $pc
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
}

/**
 * API: Cambiar estado de PC (Activar/Desactivar)
 */
public function cambiarEstadoPC(): void
{
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar si la PC tiene sesiones activas antes de desactivar
    if ($activa == 0) {
        $tieneSesionActiva = $this->model->tieneSesionActiva($id);
        if ($tieneSesionActiva) {
            echo json_encode([
                'success' => false, 
                'message' => 'No se puede desactivar una PC con sesiones activas'
            ]);
            return;
        }
    }
    
    $resultado = $this->model->cambiarEstadoPC($id, $activa);
    
    if ($resultado['success']) {
        $pc = $this->model->obtenerPC($id);
        echo json_encode([
            'success' => true,
            'message' => $activa ? 'PC activada' : 'PC desactivada',
            'data' => $pc
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
}

/**
 * API: Eliminar PC (solo si no tiene sesiones)
 */
public function eliminarPC(): void
{
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Verificar si la PC tiene sesiones (activas o históricas)
    $tieneSesiones = $this->model->tieneSesiones($id);
    if ($tieneSesiones) {
        echo json_encode([
            'success' => false, 
            'message' => 'No se puede eliminar una PC con sesiones registradas. Desactívala en su lugar.'
        ]);
        return;
    }
    
    $resultado = $this->model->eliminarPC($id);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'PC eliminada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $resultado['message']
        ]);
    }
}

/**
 * API: Obtener tipos de activo para el selector
 */
public function obtenerTiposActivo(): void
{
    header('Content-Type: application/json');
    
    $tipos = $this->model->obtenerTiposActivo();
    echo json_encode(['success' => true, 'data' => $tipos]);
}
    
}

