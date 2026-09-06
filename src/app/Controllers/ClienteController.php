<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Cliente;

class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }

    public function getModel(): Cliente
    {
        return $this->model;
    }

    public function setModel(Cliente $model): void
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
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el cliente tiene registros asociados.']);
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
        $id = Validator::id($_GET['id'] ?? null, 'ID del cliente');
        $cliente = $this->model->obtenerClientePorId($id);
        if ($cliente) {
            echo json_encode(['success' => true, 'data' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $cedula    = Validator::cedula($_POST['cedula'] ?? null, 'cédula');
        $nombre    = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos']);
        $apellido  = Validator::texto($_POST['apellido'] ?? null, 'apellido', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El apellido contiene caracteres no permitidos']);
        $direccion = Validator::texto($_POST['direccion'] ?? null, 'dirección', ['required' => false, 'max' => 500]);
        $telefono  = Validator::telefono($_POST['telefono'] ?? null, 'teléfono');

        if ($this->model->existeCedula($cedula)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe un cliente con esa cédula']);
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
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id        = Validator::id($_POST['id'] ?? null, 'ID del cliente');
        $cedula    = Validator::cedula($_POST['cedula'] ?? null, 'cédula');
        $nombre    = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos']);
        $apellido  = Validator::texto($_POST['apellido'] ?? null, 'apellido', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El apellido contiene caracteres no permitidos']);
        $direccion = Validator::texto($_POST['direccion'] ?? null, 'dirección', ['required' => false, 'max' => 500]);
        $telefono  = Validator::telefono($_POST['telefono'] ?? null, 'teléfono');

        if ($this->model->existeCedula($cedula, $id)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe otro cliente con esa cédula']);
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
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del cliente');
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
