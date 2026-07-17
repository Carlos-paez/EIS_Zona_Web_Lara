<?php
// =============================================================================
// CONTROLADOR ProveedorGestionController (API JSON para gestión de proveedores)
// =============================================================================
// Propósito: Manejar las peticiones AJAX del módulo de gestión de proveedores.
//            Responde siempre en formato JSON. CRUD de proveedores.
// =============================================================================

namespace App\Controllers;

use App\Models\ProveedorGestion;

class ProveedorGestionController
{
    private ProveedorGestion $model;

    public function __construct()
    {
        $this->model = new ProveedorGestion();
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'       => $this->listar(),
                'detalle'      => $this->detalle(),
                'crear'        => $this->crear(),
                'actualizar'   => $this->actualizar(),
                'eliminar'     => $this->eliminar(),
                'kpis'         => $this->kpis(),
                default        => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
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
        $rif      = $_POST['rif'] ?? '';
        $nombre   = $_POST['nombre'] ?? '';
        $email    = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        if (empty($rif) || empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'RIF y Nombre son obligatorios']);
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
        $id       = (int)($_POST['id'] ?? 0);
        $rif      = $_POST['rif'] ?? '';
        $nombre   = $_POST['nombre'] ?? '';
        $email    = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        if (!$id || empty($rif) || empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'ID, RIF y Nombre son obligatorios']);
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
