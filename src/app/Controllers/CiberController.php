<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\CiberControl;

/**
 * Controlador del módulo Control de Cybercafé.
 *
 * Proporciona tanto el renderizado de la página (index) como el API
 * AJAX (?pagina=ciberControl&action=...) consumido por app.cyber.js.
 */
class CiberController
{
    private CiberControl $model;

    public function __construct()
    {
        $this->model = new CiberControl();
    }

    public function getModel(): CiberControl
    {
        return $this->model;
    }

    public function setModel(CiberControl $model): void
    {
        $this->model = $model;
    }

    /**
     * Despacha las peticiones AJAX del módulo.
     */
    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'estaciones'    => $this->estaciones(),
                'tarifas'       => $this->tarifas(),
                'buscarCliente' => $this->buscarCliente(),
                'iniciar'       => $this->iniciar(),
                'finalizar'     => $this->finalizar(),
                'estadisticas'  => $this->estadisticas(),
                'historial'     => $this->historial(),
                'tiposActivo'   => $this->tiposActivo(),
                'obtenerPC'     => $this->obtenerPC(),
                'crearPC'       => $this->crearPC(),
                'actualizarPC'  => $this->actualizarPC(),
                'eliminarPC'    => $this->eliminarPC(),
                'cambiarEstadoPC' => $this->cambiarEstadoPC(),
                default         => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Página principal del módulo (renderizada en el layout).
     */
    public function index(): void
    {
        try {
            $estaciones = $this->model->listarEstaciones();
        } catch (\Throwable $e) {
            $estaciones = [];
        }

        $totalEstaciones = count($estaciones);
        $countDisponibles = 0;
        $countOcupadas = 0;
        $countMantenimiento = 0;

        foreach ($estaciones as $e) {
            if (($e['estado'] ?? '') === 'ocupada') {
                $countOcupadas++;
            } elseif (($e['estado'] ?? '') === 'disponible') {
                $countDisponibles++;
            } else {
                $countMantenimiento++;
            }
        }

        $clientes = [];
        try {
            $clientes = $this->model->listarEstaciones();
        } catch (\Throwable $e) {
            $clientes = [];
        }

        $tarifas = [];
        try {
            $tarifas = $this->model->listarTarifas();
        } catch (\Throwable $e) {
            $tarifas = [];
        }

        $tiposActivo = [];
        try {
            $tiposActivo = $this->model->listarTiposActivo();
        } catch (\Throwable $e) {
            $tiposActivo = [];
        }

        $pageTitle  = 'Control de Cybercafé';
        $headerExtra = '<span class="chip green white-text">' . $countDisponibles . ' Disponibles</span>'
                     . '<span class="chip orange white-text">' . $countOcupadas . ' Ocupadas</span>';
        $contentView = __DIR__ . '/../Views/ciberControl.php';
        $pagina      = 'ciberControl';

        require __DIR__ . '/../template/layout.php';
    }

    /**
     * API: Lista de estaciones (activas de cybercafé) con su estado.
     */
    private function estaciones(): void
    {
        $estaciones = $this->model->listarEstaciones();
        echo json_encode(['success' => true, 'data' => $estaciones]);
    }

    /**
     * API: Tarifas disponibles.
     */
    private function tarifas(): void
    {
        echo json_encode(['success' => true, 'data' => $this->model->listarTarifas()]);
    }

    /**
     * API: Busca un cliente por cédula para precargar el formulario.
     */
    private function buscarCliente(): void
    {
        $cedula = trim($_GET['cedula'] ?? '');
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => null]);
            return;
        }
        $cliente = $this->model->buscarCliente($cedula);
        echo json_encode(['success' => true, 'data' => $cliente ?: null]);
    }

    /**
     * API: Inicia una sesión de cybercafé.
     */
    private function iniciar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $ciudadano  = trim($_POST['ciudadano'] ?? '');
        $cedula     = trim($_POST['cedula'] ?? '');
        $direccion  = trim($_POST['direccion'] ?? '');
        $telefono   = trim($_POST['telefono'] ?? '');
        $activoId   = (int)($_POST['activo_id'] ?? 0);
        $tarifaId   = (int)($_POST['tarifa_id'] ?? 0);
        $tiempoUso  = trim($_POST['tiempo_uso'] ?? '');

        $sesionId = $this->model->iniciarSesion($ciudadano, $cedula, $direccion, $telefono, $activoId, $tarifaId, $tiempoUso);

        echo json_encode(
            $sesionId
                ? ['success' => true, 'message' => 'Sesión iniciada correctamente', 'data' => ['sesion_id' => $sesionId]]
                : ['success' => false, 'error' => 'No se pudo iniciar la sesión']
        );
    }

    /**
     * API: Finaliza una sesión de cybercafé.
     */
    private function finalizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $sesionId = (int)($_POST['sesion_id'] ?? 0);
        if ($sesionId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de sesión no válido']);
            return;
        }

        $resultado = $this->model->finalizarSesion($sesionId);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Sesión finalizada correctamente']
                : ['success' => false, 'error' => 'No se pudo finalizar la sesión']
        );
    }

    /**
     * API: Estadísticas del cyber.
     */
    private function estadisticas(): void
    {
        $estaciones = $this->model->listarEstaciones();
        $total = count($estaciones);
        $ocupadas = count(array_filter($estaciones, fn($e) => ($e['estado'] ?? '') === 'ocupada'));
        $disponibles = $total - $ocupadas;

        echo json_encode([
            'success' => true,
            'data' => [
                'total'      => $total,
                'disponibles' => $disponibles,
                'ocupadas'   => $ocupadas,
                'mantenimiento' => 0,
            ]
        ]);
    }

    /**
     * API: Historial de sesiones de una estación.
     */
    private function historial(): void
    {
        $activoId = (int)($_GET['activo_id'] ?? 0);
        if ($activoId <= 0) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }
        $historial = $this->model->historialEstacion($activoId);
        echo json_encode(['success' => true, 'data' => $historial]);
    }

    /**
     * API: Tipos de activo para el selector de PC.
     */
    private function tiposActivo(): void
    {
        echo json_encode(['success' => true, 'data' => $this->model->listarTiposActivo()]);
    }

    /**
     * API: Obtener una PC para editar.
     */
    private function obtenerPC(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            return;
        }
        $pc = $this->model->obtenerPC($id);
        echo json_encode(
            $pc
                ? ['success' => true, 'data' => $pc]
                : ['success' => false, 'error' => 'PC no encontrada']
        );
    }

    /**
     * API: Crear una PC (activo de cybercafé).
     */
    private function crearPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $marca       = trim($_POST['marca'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $tipoActivo  = (int)($_POST['tipo_activo_id'] ?? 0);
        $activa      = (int)($_POST['activa'] ?? 1);

        if ($tipoActivo <= 0) {
            echo json_encode(['success' => false, 'error' => 'Debes seleccionar un tipo de PC']);
            return;
        }

        $id = $this->model->crearPC($marca, $descripcion, $tipoActivo, $activa);
        echo json_encode(
            $id
                ? ['success' => true, 'message' => 'PC creada exitosamente', 'data' => ['id' => $id]]
                : ['success' => false, 'error' => 'Error al crear la PC']
        );
    }

    /**
     * API: Actualizar una PC.
     */
    private function actualizarPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $marca       = trim($_POST['marca'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $tipoActivo  = (int)($_POST['tipo_activo_id'] ?? 0);
        $activa      = (int)($_POST['activa'] ?? 1);

        if ($id <= 0 || $tipoActivo <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
            return;
        }

        $resultado = $this->model->actualizarPC($id, $marca, $descripcion, $tipoActivo, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'PC actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la PC']
        );
    }

    /**
     * API: Cambiar el estado activa/desactivada de una PC.
     */
    private function cambiarEstadoPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id     = (int)($_POST['id'] ?? 0);
        $activa = (int)($_POST['activa'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            return;
        }

        $resultado = $this->model->cambiarEstadoPC($id, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activa ? 'PC activada' : 'PC desactivada']
                : ['success' => false, 'error' => 'Error al cambiar el estado']
        );
    }

    /**
     * API: Eliminar una PC (solo si no tiene sesiones).
     */
    private function eliminarPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            return;
        }

        $resultado = $this->model->eliminarPC($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'PC eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la PC']
        );
    }

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
