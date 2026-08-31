<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\ProveedorGestion;

class ProveedorGestionController
{
    private ProveedorGestion $model;

    public function __construct()
    {
        $this->model = new ProveedorGestion();
    }

    public function getModel(): ProveedorGestion
    {
        return $this->model;
    }

    public function setModel(ProveedorGestion $model): void
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
        $proveedores = $this->model->obtenerProveedores();
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }

    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $this->model->totalProveedores(),
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
        $proveedor = $this->model->obtenerProveedorPorId($id);
        if ($proveedor) {
            echo json_encode(['success' => true, 'data' => $proveedor]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Proveedor no encontrado']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $rif      = trim($_POST['rif'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($rif) || empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'RIF y Nombre son obligatorios']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'El formato del email no es válido']);
            return;
        }

        if ($this->model->existeRif($rif)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe un proveedor con ese RIF']);
            return;
        }

        $resultado = $this->model->crearProveedor($rif, $nombre, $email, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Proveedor creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el proveedor']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $rif      = trim($_POST['rif'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!$id || empty($rif) || empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'ID, RIF y Nombre son obligatorios']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'El formato del email no es válido']);
            return;
        }

        if ($this->model->existeRif($rif, $id)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe otro proveedor con ese RIF']);
            return;
        }

        $resultado = $this->model->actualizarProveedor($id, $rif, $nombre, $email, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Proveedor actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el proveedor']
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
        $resultado = $this->model->eliminarProveedor($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Proveedor eliminado exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar el proveedor']
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
