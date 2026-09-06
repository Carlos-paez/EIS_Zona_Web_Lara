<?php

namespace App\Controllers;

use App\Core\Router;
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
                default      => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el activo tiene registros asociados.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
            }
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
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
            echo json_encode(['success' => false, 'error' => 'Activo no encontrado']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $marca       = Validator::texto($_POST['marca'] ?? null, 'marca', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'La marca contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $tipoActivo  = Validator::id($_POST['tipo_activo_id'] ?? null, 'tipo de activo');
        $activa      = isset($_POST['activa']) ? (int)$_POST['activa'] : 0;
        $isCiber     = isset($_POST['is_ciber']) ? (int)$_POST['is_ciber'] : 0;

        $resultado = $this->model->crearActivo($marca, $descripcion, $tipoActivo, $activa, $isCiber);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el activo']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id          = Validator::id($_POST['id'] ?? null, 'ID del activo');
        $marca       = Validator::texto($_POST['marca'] ?? null, 'marca', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'La marca contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $tipoActivo  = Validator::id($_POST['tipo_activo_id'] ?? null, 'tipo de activo');
        $activa      = isset($_POST['activa']) ? (int)$_POST['activa'] : 0;
        $isCiber     = isset($_POST['is_ciber']) ? (int)$_POST['is_ciber'] : 0;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }

        $resultado = $this->model->actualizarActivo($id, $marca, $descripcion, $tipoActivo, $activa, $isCiber);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el activo']
        );
    }

    private function estado(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id     = Validator::id($_POST['id'] ?? null, 'ID del activo');
        $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 0;

        $resultado = $this->model->cambiarEstadoActivo($id, $activa);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activa ? 'Activo activado' : 'Activo desactivado']
                : ['success' => false, 'error' => 'Error al cambiar el estado']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del activo');
        $resultado = $this->model->eliminarActivo($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Activo eliminado exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar el activo']
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
