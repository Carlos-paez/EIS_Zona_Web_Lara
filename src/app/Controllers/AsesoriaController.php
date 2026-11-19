<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Asesoria;
use App\Models\Cliente;

class AsesoriaController extends BaseController
{
    private Asesoria $model;

    public function __construct()
    {
        $this->setModel(new Asesoria());
    }

    public function getModel(): Asesoria
    {
        return $this->model;
    }

    public function setModel(Asesoria $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $this->cargarRequest();

        $action = $this->get('action', '');

        try {
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'buscar'     => $this->buscar(),
                'clientes'   => $this->clientes(),
                'crear'      => $this->crear(),
                'kpis'       => $this->kpis(),
                default      => $this->json(false, null, 'La acción solicitada no es válida.'),
            };
        } catch (ValidationException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'fieldErrors' => $e->errores()]);
        } catch (\PDOException $e) {
            Logger::error($e, 'Asesorías - consulta SQL');
            echo json_encode(['success' => false, 'error' => 'No se pudo conectar con la base de datos. Intenta de nuevo.']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Asesorías');
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error interno. Intenta de nuevo.']);
        }
    }

    private function listar(): void
    {
        $asesorias = $this->getModel()->obtenerTodas();
        echo json_encode(['success' => true, 'data' => $asesorias]);
    }

    private function kpis(): void
    {
        $total = $this->getModel()->obtenerTodas();
        $porEstado = $this->getModel()->contarPorEstado();

        $perm = 0;
        $den  = 0;
        foreach ($porEstado as $fila) {
            if (($fila['estado'] ?? '') === 'Permitido') {
                $perm = (int)$fila['total'];
            } elseif (($fila['estado'] ?? '') === 'Denegado') {
                $den = (int)$fila['total'];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'total'     => count($total),
                'permitidas' => $perm,
                'derivadas' => $den,
            ]
        ]);
    }

    private function detalle(): void
    {
        $id = Validator::id($this->get('id'), 'ID de la asesoría');
        $this->set('id', $id);
        $asesoria = $this->getModel()->obtenerPorId($id);
        if ($asesoria) {
            echo json_encode(['success' => true, 'data' => $asesoria]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se encontró la asesoría.']);
        }
    }

    private function buscar(): void
    {
        $cedula = Validator::busqueda($this->get('cedula', ''), 'cédula');
        $this->set('cedula', $cedula);
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }
        $resultados = $this->getModel()->buscarPorCedula($cedula);
        echo json_encode(['success' => true, 'data' => $resultados]);
    }

    /**
     * API: Lista de clientes registrados para el selector del formulario.
     */
    private function clientes(): void
    {
        $clientes = (new Cliente())->obtenerClientes();
        $this->json(true, $clientes);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'ciudadano'   => ['label' => 'ciudadano', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 200, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El ciudadano contiene caracteres no permitidos'],
            'cedula'      => ['label' => 'cédula', 'type' => 'cedula'],
            'documento'   => ['label' => 'tipo de documento', 'type' => 'texto', 'required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos'],
            'descripcion' => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
            'direccion'   => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.direccion'), 'patternMessage' => 'La dirección contiene caracteres no permitidos'],
            'telefono'    => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $ciudadano   = $datos['ciudadano'];
        $cedula      = $datos['cedula'];
        $documento   = $datos['documento'];
        $descripcion = $datos['descripcion'];
        $direccion   = $datos['direccion'];
        $telefono    = $datos['telefono'];

        $this->set('ciudadano', $ciudadano)
            ->set('cedula', $cedula)
            ->set('documento', $documento)
            ->set('descripcion', $descripcion)
            ->set('direccion', $direccion)
            ->set('telefono', $telefono);

        $resultado = $this->getModel()->crear($ciudadano, $cedula, $documento, $descripcion, $direccion, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Asesoría registrada exitosamente']
                : ['success' => false, 'error' => 'No se pudo registrar la asesoría. Intenta de nuevo.']
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
