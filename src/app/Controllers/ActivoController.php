<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Activo;

class ActivoController
{
    private Activo $model;

    public function __construct()
    {
        $this->model = new Activo();
    }

    public function getModel(): Activo
    {
        return $this->model;
    }

    public function setModel(Activo $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'crear'      => $this->crear(),
                'actualizar' => $this->actualizar(),
                'estado'     => $this->estado(),
                'eliminar'   => $this->eliminar(),
                'kpis'       => $this->kpis(),
                'tipos'      => $this->tipos(),
                default      => $this->json(false, null, 'La acción solicitada no es válida.'),
            };
        } catch (ValidationException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'fieldErrors' => $e->errores()]);
        } catch (\PDOException $e) {
            Logger::error($e, 'Activos - consulta SQL');
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar el activo porque tiene registros asociados.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo conectar con la base de datos. Intenta de nuevo.']);
            }
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Activos');
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error interno. Intenta de nuevo.']);
        }
    }

    private function listar(): void
    {
        $activos = $this->model->obtenerActivos();
        echo json_encode(['success' => true, 'data' => $activos]);
    }

    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total'     => $this->model->totalActivos(),
                'ciber'     => $this->model->totalCiber(),
                'ocupados'  => $this->model->totalActivosOcupados(),
                'inactivos' => $this->model->totalInactivos(),
            ]
        ]);
    }

    private function tipos(): void
    {
        echo json_encode(['success' => true, 'data' => $this->model->listarTiposActivo()]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del activo');
        $activo = $this->model->obtenerActivoPorId($id);
        if ($activo) {
            echo json_encode(['success' => true, 'data' => $activo]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se encontró el activo.']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = Validator::validar($_POST, [
            'marca'          => ['label' => 'marca', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'La marca contiene caracteres no permitidos'],
            'descripcion'    => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
            'tipo_activo_id' => ['label' => 'tipo de activo', 'type' => 'id'],
            'activa'         => ['label' => 'estado', 'type' => 'bool', 'required' => false],
            'is_ciber'       => ['label' => 'ciber', 'type' => 'bool', 'required' => false],
        ]);

        $marca       = $datos['marca'];
        $descripcion = $datos['descripcion'];
        $tipoActivo  = $datos['tipo_activo_id'];
        $activa      = $datos['activa'];
        $isCiber     = $datos['is_ciber'];

        $resultado = $this->model->crearActivo($marca, $descripcion, $tipoActivo, $activa, $isCiber);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo creado exitosamente']
                : ['success' => false, 'error' => 'No se pudo crear el activo. Intenta de nuevo.']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = Validator::validar($_POST, [
            'id'             => ['label' => 'ID del activo', 'type' => 'id'],
            'marca'          => ['label' => 'marca', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'La marca contiene caracteres no permitidos'],
            'descripcion'    => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
            'tipo_activo_id' => ['label' => 'tipo de activo', 'type' => 'id'],
            'activa'         => ['label' => 'estado', 'type' => 'bool', 'required' => false],
            'is_ciber'       => ['label' => 'ciber', 'type' => 'bool', 'required' => false],
        ]);

        $id          = $datos['id'];
        $marca       = $datos['marca'];
        $descripcion = $datos['descripcion'];
        $tipoActivo  = $datos['tipo_activo_id'];
        $activa      = $datos['activa'];
        $isCiber     = $datos['is_ciber'];

        $resultado = $this->model->actualizarActivo($id, $marca, $descripcion, $tipoActivo, $activa, $isCiber);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo actualizado exitosamente']
                : ['success' => false, 'error' => 'No se pudo actualizar el activo. Intenta de nuevo.']
        );
    }

    private function estado(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $id     = Validator::id($_POST['id'] ?? null, 'ID del activo');
        $activa = Validator::bool($_POST['activa'] ?? null, 'estado', true);

        $resultado = $this->model->cambiarEstadoActivo($id, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activa ? 'Activo activado' : 'Activo desactivado']
                : ['success' => false, 'error' => 'No se pudo cambiar el estado. Intenta de nuevo.']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del activo');
        $resultado = $this->model->eliminarActivo($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo eliminado exitosamente']
                : ['success' => false, 'error' => 'No se pudo eliminar el activo. Intenta de nuevo.']
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
