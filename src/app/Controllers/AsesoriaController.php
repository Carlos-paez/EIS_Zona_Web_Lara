<?php

namespace App\Controllers;

use App\Core\Router;
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
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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
        $resultados = $this->model->buscarPorCedula($cedula);
        echo json_encode(['success' => true, 'data' => $resultados]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $ciudadano   = trim($_POST['ciudadano'] ?? '');
        $cedula      = trim($_POST['cedula'] ?? '');
        $documento   = trim($_POST['documento'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $direccion   = trim($_POST['direccion'] ?? '');
        $telefono    = trim($_POST['telefono'] ?? '');

        if (empty($ciudadano) || empty($cedula) || empty($documento)) {
            echo json_encode(['success' => false, 'error' => 'Ciudadano, cédula y tipo de documento son obligatorios']);
            return;
        }

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

        $id          = (int)($_POST['id'] ?? 0);
        $documento   = trim($_POST['documento'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$id || empty($documento)) {
            echo json_encode(['success' => false, 'error' => 'ID y tipo de documento son obligatorios']);
            return;
        }

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

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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
