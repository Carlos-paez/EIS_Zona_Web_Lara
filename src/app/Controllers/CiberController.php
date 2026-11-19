<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\CiberControl;
use App\Models\Cliente;

/**
 * Controlador del módulo Control de Cybercafé.
 *
 * Proporciona tanto el renderizado de la página (index) como el API
 * AJAX (?pagina=ciberControl&action=...) consumido por app.cyber.js.
 */
class CiberController extends BaseController
{
    private CiberControl $model;

    public function __construct()
    {
        $this->setModel(new CiberControl());
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

        $this->cargarRequest();

        $action = $this->get('action', '');

        try {
            match ($action) {
                'estaciones'    => $this->estaciones(),
                'tarifas'       => $this->tarifas(),
                'clientes'      => $this->clientes(),
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
                default         => $this->json(false, null, 'La acción solicitada no es válida.'),
            };
        } catch (ValidationException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'fieldErrors' => $e->errores()]);
        } catch (\PDOException $e) {
            Logger::error($e, 'Cyber (CiberController) - consulta SQL');
            echo json_encode(['success' => false, 'error' => 'No se pudo conectar con la base de datos. Intenta de nuevo.']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Cyber (CiberController)');
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error interno. Intenta de nuevo.']);
        }
    }

    /**
     * Datos iniciales de la vista. Antes este método renderizaba el layout
     * por su cuenta; ahora solo prepara datos y el Router se encarga de
     * inyectarlos en la vista (C → M → BD → C → V).
     *
     * @return array<string,mixed>
     */
    public function index(): array
    {
        return [
            'totalEstaciones' => $this->seguro(fn() => count($this->getModel()->listarEstaciones()), 0),
            'tiposActivo'     => $this->seguro(fn() => $this->getModel()->listarTiposActivo(), []),
        ];
    }

    /**
     * API: Lista de estaciones (activas de cybercafé) con su estado.
     */
    private function estaciones(): void
    {
        $estaciones = $this->getModel()->listarEstaciones();
        echo json_encode(['success' => true, 'data' => $estaciones]);
    }

    /**
     * API: Tarifas disponibles.
     */
    private function tarifas(): void
    {
        echo json_encode(['success' => true, 'data' => $this->getModel()->listarTarifas()]);
    }

    /**
     * API: Lista de clientes registrados para el selector del formulario.
     */
    private function clientes(): void
    {
        $clientes = (new Cliente())->obtenerClientes();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    /**
     * API: Busca un cliente por cédula para precargar el formulario.
     */
    private function buscarCliente(): void
    {
        $cedula = Validator::busqueda($this->get('cedula', ''), 'cédula');
        $this->set('cedula', $cedula);
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => null]);
            return;
        }
        $cliente = $this->getModel()->buscarCliente($cedula);
        echo json_encode(['success' => true, 'data' => $cliente ?: null]);
    }

    /**
     * API: Inicia una sesión de cybercafé.
     */
    private function iniciar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'ciudadano'  => ['label' => 'ciudadano', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre del ciudadano contiene caracteres no permitidos'],
            'cedula'     => ['label' => 'cédula', 'type' => 'cedula'],
            'direccion'  => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.direccion'), 'patternMessage' => 'La dirección contiene caracteres no permitidos'],
            'telefono'   => ['label' => 'teléfono', 'type' => 'telefono'],
            'activo_id'  => ['label' => 'equipo (PC)', 'type' => 'id'],
            'tarifa_id'  => ['label' => 'tarifa', 'type' => 'id'],
            'tiempo_uso' => ['label' => 'tiempo de uso', 'type' => 'tiempoUso'],
        ]);

        $ciudadano = $datos['ciudadano'];
        $cedula    = $datos['cedula'];
        $direccion = $datos['direccion'];
        $telefono  = $datos['telefono'];
        $activoId  = $datos['activo_id'];
        $tarifaId  = $datos['tarifa_id'];
        $tiempoUso = $datos['tiempo_uso'];

        $this->set('ciudadano', $ciudadano)
            ->set('cedula', $cedula)
            ->set('direccion', $direccion)
            ->set('telefono', $telefono)
            ->set('activo_id', $activoId)
            ->set('tarifa_id', $tarifaId)
            ->set('tiempo_uso', $tiempoUso);

        $sesionId = $this->getModel()->iniciarSesion(
            $ciudadano,
            $cedula,
            $direccion,
            $telefono,
            $activoId,
            $tarifaId,
            $tiempoUso
        );

        echo json_encode(
            $sesionId
                ? ['success' => true, 'message' => 'Sesión iniciada correctamente', 'data' => ['sesion_id' => $sesionId]]
                : ['success' => false, 'error' => 'No se pudo iniciar la sesión. Intenta de nuevo.']
        );
    }

    /**
     * API: Finaliza una sesión de cybercafé.
     */
    private function finalizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $sesionId = Validator::id($this->get('sesion_id'), 'ID de sesión');
        $this->set('sesion_id', $sesionId);

        $resultado = $this->getModel()->finalizarSesion($sesionId);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Sesión finalizada correctamente']
                : ['success' => false, 'error' => 'No se pudo finalizar la sesión. Intenta de nuevo.']
        );
    }

    /**
     * API: Estadísticas del cyber.
     */
    private function estadisticas(): void
    {
        $estaciones = $this->getModel()->listarEstaciones();
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
        $activoId = Validator::id($this->get('activo_id'), 'ID del equipo');
        $this->set('activo_id', $activoId);
        $historial = $this->getModel()->historialEstacion($activoId);
        echo json_encode(['success' => true, 'data' => $historial]);
    }

    /**
     * API: Tipos de activo para el selector de PC.
     */
    private function tiposActivo(): void
    {
        echo json_encode(['success' => true, 'data' => $this->getModel()->listarTiposActivo()]);
    }

    /**
     * API: Obtener una PC para editar.
     */
    private function obtenerPC(): void
    {
        $id = Validator::id($this->get('id'), 'ID de la PC');
        $this->set('id', $id);
        $pc = $this->getModel()->obtenerPC($id);
        echo json_encode(
            $pc
                ? ['success' => true, 'data' => $pc]
                : ['success' => false, 'error' => 'No se encontró la PC.']
        );
    }

    /**
     * API: Crear una PC (activo de cybercafé).
     */
    private function crearPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'marca'          => ['label' => 'marca', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'La marca contiene caracteres no permitidos'],
            'descripcion'    => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
            'tipo_activo_id' => ['label' => 'tipo de PC', 'type' => 'id'],
            'activa'         => ['label' => 'estado', 'type' => 'bool', 'required' => false],
        ]);

        $marca       = $datos['marca'];
        $descripcion = $datos['descripcion'];
        $tipoActivo  = $datos['tipo_activo_id'];
        $activa      = $datos['activa'];

        $this->set('marca', $marca)
            ->set('descripcion', $descripcion)
            ->set('tipo_activo_id', $tipoActivo)
            ->set('activa', $activa);

        $id = $this->getModel()->crearPC($marca, $descripcion, $tipoActivo, $activa);
        echo json_encode(
            $id
                ? ['success' => true, 'message' => 'PC creada exitosamente', 'data' => ['id' => $id]]
                : ['success' => false, 'error' => 'No se pudo crear la PC. Intenta de nuevo.']
        );
    }

    /**
     * API: Actualizar una PC.
     */
    private function actualizarPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'id'             => ['label' => 'ID de la PC', 'type' => 'id'],
            'marca'          => ['label' => 'marca', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'La marca contiene caracteres no permitidos'],
            'descripcion'    => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
            'tipo_activo_id' => ['label' => 'tipo de PC', 'type' => 'id'],
            'activa'         => ['label' => 'estado', 'type' => 'bool', 'required' => false],
        ]);

        $id          = $datos['id'];
        $marca       = $datos['marca'];
        $descripcion = $datos['descripcion'];
        $tipoActivo  = $datos['tipo_activo_id'];
        $activa      = $datos['activa'];

        $this->set('id', $id)
            ->set('marca', $marca)
            ->set('descripcion', $descripcion)
            ->set('tipo_activo_id', $tipoActivo)
            ->set('activa', $activa);

        $resultado = $this->getModel()->actualizarPC($id, $marca, $descripcion, $tipoActivo, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'PC actualizada exitosamente']
                : ['success' => false, 'error' => 'No se pudo actualizar la PC. Intenta de nuevo.']
        );
    }

    /**
     * API: Cambiar el estado activa/desactivada de una PC.
     */
    private function cambiarEstadoPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $id     = Validator::id($this->get('id'), 'ID de la PC');
        $activa = Validator::bool($this->get('activa'), 'estado', true);
        $this->set('id', $id)->set('activa', $activa);

        $resultado = $this->getModel()->cambiarEstadoPC($id, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activa ? 'PC activada' : 'PC desactivada']
                : ['success' => false, 'error' => 'No se pudo cambiar el estado. Intenta de nuevo.']
        );
    }

    /**
     * API: Eliminar una PC (solo si no tiene sesiones).
     */
    private function eliminarPC(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'El método de la solicitud no está permitido.']);
            return;
        }
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $id = Validator::id($this->get('id'), 'ID de la PC');
        $this->set('id', $id);

        $resultado = $this->getModel()->eliminarPC($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'PC eliminada exitosamente']
                : ['success' => false, 'error' => 'No se pudo eliminar la PC. Intenta de nuevo.']
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
