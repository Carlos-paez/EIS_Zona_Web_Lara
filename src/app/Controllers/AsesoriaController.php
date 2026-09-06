<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Asesoria;

class AsesoriaController
{
    private Asesoria $model;

    public function __construct()
    {
        $this->model = new Asesoria();
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

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'buscar'     => $this->buscar(),
                'crear'      => $this->crear(),
                'actualizar' => $this->actualizar(),
                'eliminar'   => $this->eliminar(),
                'kpis'       => $this->kpis(),
                default      => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function listar(): void
    {
        $asesorias = $this->model->obtenerTodas();
        echo json_encode(['success' => true, 'data' => $asesorias]);
    }

    private function kpis(): void
    {
        $total = $this->model->obtenerTodas();
        $porEstado = $this->model->contarPorEstado();

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
        $id = Validator::id($_GET['id'] ?? null, 'ID de la asesoría');
        $asesoria = $this->model->obtenerPorId($id);
        if ($asesoria) {
            echo json_encode(['success' => true, 'data' => $asesoria]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Asesoría no encontrada']);
        }
    }

    private function buscar(): void
    {
        $cedula = trim($_GET['cedula'] ?? '');
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }
        if (!preg_match(Validator::PATTERN_CEDULA, $cedula)) {
            echo json_encode(['success' => false, 'error' => 'La cédula no tiene un formato válido']);
            return;
        }
        $resultados = $this->model->buscarPorCedula($cedula);
        echo json_encode(['success' => true, 'data' => $resultados]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $ciudadano   = Validator::texto($_POST['ciudadano'] ?? null, 'ciudadano', ['required' => true, 'min' => 2, 'max' => 200, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El ciudadano contiene caracteres no permitidos']);
        $cedula      = Validator::cedula($_POST['cedula'] ?? null, 'cédula');
        $documento   = Validator::texto($_POST['documento'] ?? null, 'tipo de documento', ['required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $direccion   = Validator::texto($_POST['direccion'] ?? null, 'dirección', ['required' => false, 'max' => 500]);
        $telefono    = Validator::telefono($_POST['telefono'] ?? null, 'teléfono');

        $resultado = $this->model->crear($ciudadano, $cedula, $documento, $descripcion, $direccion, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Asesoría registrada exitosamente']
                : ['success' => false, 'error' => 'Error al registrar la asesoría']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id          = Validator::id($_POST['id'] ?? null, 'ID de la asesoría');
        $documento   = Validator::texto($_POST['documento'] ?? null, 'tipo de documento', ['required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);

        $resultado = $this->model->actualizar($id, $documento, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Asesoría actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la asesoría']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la asesoría');
        $resultado = $this->model->eliminar($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Asesoría eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la asesoría']
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
