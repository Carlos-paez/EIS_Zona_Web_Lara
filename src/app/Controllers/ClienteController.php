<?php

namespace App\Controllers;

use App\Models\Cliente;

class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
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
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el cliente tiene registros asociados (asesorías, ventas o sesiones).']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $msg]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function listar(): void
    {
        $clientes = $this->model->obtenerClientes();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $this->model->totalClientes(),
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
        $cliente = $this->model->obtenerClientePorId($id);
        if ($cliente) {
            echo json_encode(['success' => true, 'data' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
        }
    }

    private function crear(): void
    {
        $cedula    = trim($_POST['cedula'] ?? '');
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($direccion) || empty($telefono)) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
            return;
        }

        $resultado = $this->model->crearCliente($cedula, $nombre, $apellido, $direccion, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Cliente creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el cliente']
        );
    }

    private function actualizar(): void
    {
        $id        = (int)($_POST['id'] ?? 0);
        $cedula    = trim($_POST['cedula'] ?? '');
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        if (!$id || empty($cedula) || empty($nombre) || empty($apellido) || empty($direccion) || empty($telefono)) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
            return;
        }

        $resultado = $this->model->actualizarCliente($id, $cedula, $nombre, $apellido, $direccion, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Cliente actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el cliente']
        );
    }

    private function eliminar(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $resultado = $this->model->eliminarCliente($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Cliente eliminado exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar el cliente']
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
