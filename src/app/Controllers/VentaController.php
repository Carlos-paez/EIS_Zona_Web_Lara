<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\Cliente;
use App\Models\Venta;

class VentaController
{
    private Venta $model;

    public function __construct()
    {
        $this->model = new Venta();
    }

    public function getModel(): Venta
    {
        return $this->model;
    }

    public function setModel(Venta $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'productos'     => $this->productos(),
                'clientes'      => $this->clientes(),
                'buscarCliente' => $this->buscarCliente(),
                'registrar'     => $this->registrar(),
                default         => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function productos(): void
    {
        $productos = $this->model->listarProductos();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    private function clientes(): void
    {
        $clientes = (new Cliente())->obtenerClientes();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    private function buscarCliente(): void
    {
        $cedula = trim($_GET['cedula'] ?? '');
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => null]);
            return;
        }
        $cliente = (new Cliente())->obtenerClientePorCedula($cedula);
        echo json_encode(['success' => true, 'data' => $cliente ?: null]);
    }

    private function registrar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $ciudadano = trim($_POST['ciudadano'] ?? '');
        $cedula    = trim($_POST['cedula'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        if (empty($ciudadano) || empty($cedula)) {
            echo json_encode(['success' => false, 'error' => 'Nombre y cédula del cliente son obligatorios']);
            return;
        }

        $itemsRaw = $_POST['items'] ?? '[]';
        $items = json_decode((string)$itemsRaw, true);
        if (!is_array($items) || empty($items)) {
            echo json_encode(['success' => false, 'error' => 'El carrito está vacío']);
            return;
        }

        $usuarioId = (int)($_SESSION['user_id'] ?? 0);

        $orden_id = $this->model->registrarVenta($items, $ciudadano, $cedula, $direccion, $telefono, $usuarioId);
        echo json_encode(
            $orden_id
                ? ['success' => true, 'message' => 'Venta registrada exitosamente', 'data' => ['orden_id' => $orden_id]]
                : ['success' => false, 'error' => 'Error al registrar la venta']
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
