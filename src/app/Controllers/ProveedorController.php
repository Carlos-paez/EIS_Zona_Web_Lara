<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Proveedor;

class ProveedorController
{
    private Proveedor $model;

    public function __construct()
    {
        $this->model = new Proveedor();
    }

    public function getModel(): Proveedor
    {
        return $this->model;
    }

    public function setModel(Proveedor $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'         => $this->listar(),
                'kpis'           => $this->kpis(),
                'detalle'        => $this->detalle(),
                'productos'      => $this->productos(),
                'statuses'       => $this->statuses(),
                'crear'          => $this->crear(),
                'actualizar'     => $this->actualizar(),
                'eliminar'       => $this->eliminar(),
                'lineas'         => $this->lineas(),
                'agregarLinea'   => $this->agregarLinea(),
                'eliminarLinea'  => $this->eliminarLinea(),
                'siguienteNumero'=> $this->siguienteNumero(),
                default          => $this->json(false, null, 'Acción no válida'),
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
        $ordenes = $this->model->obtenerOrdenes();
        echo json_encode(['success' => true, 'data' => $ordenes]);
    }

    private function kpis(): void
    {
        $porEstado = $this->model->contarPorEstado();
        $pendientes = 0;
        $recibidas = 0;
        foreach ($porEstado as $row) {
            $est = strtolower($row['estado']);
            if ($est === 'pendiente' || str_contains($est, 'pend')) $pendientes = (int)$row['total'];
            elseif ($est === 'recibida' || str_contains($est, 'recib')) $recibidas = (int)$row['total'];
        }
        echo json_encode([
            'success' => true,
            'data' => [
                'total'      => $this->model->totalSolicitudes(),
                'pendientes' => $pendientes,
                'recibidas'  => $recibidas,
            ]
        ]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID de la solicitud');
        $orden = $this->model->obtenerOrdenPorId($id);
        if ($orden) {
            echo json_encode(['success' => true, 'data' => $orden]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
        }
    }

    private function productos(): void
    {
        $productos = $this->model->obtenerProductos();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    private function statuses(): void
    {
        $statuses = $this->model->obtenerStatuses();
        echo json_encode(['success' => true, 'data' => $statuses]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $numero       = Validator::numeroOrden($_POST['numero'] ?? null, 'número de orden');
        $fecha        = Validator::fecha($_POST['fecha'] ?? null, 'fecha', ['required' => true]);
        $fk_proveedor = Validator::id($_POST['fk_proveedor'] ?? null, 'proveedor');
        $fk_status    = Validator::id($_POST['fk_status'] ?? null, 'estado');

        if (!$this->model->existeProveedor($fk_proveedor)) {
            echo json_encode(['success' => false, 'error' => 'El proveedor seleccionado no existe']);
            return;
        }
        if (!$this->model->existeStatus($fk_status)) {
            echo json_encode(['success' => false, 'error' => 'El estado seleccionado no existe']);
            return;
        }

        $resultado = $this->model->crearOrden($numero, $fecha, $fk_proveedor, $fk_status);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Solicitud creada exitosamente', 'data' => ['id' => $resultado]]
                : ['success' => false, 'error' => 'Error al crear la solicitud']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id           = Validator::id($_POST['id'] ?? null, 'ID de la solicitud');
        $numero       = Validator::numeroOrden($_POST['numero'] ?? null, 'número de orden');
        $fecha        = Validator::fecha($_POST['fecha'] ?? null, 'fecha', ['required' => true]);
        $fk_proveedor = Validator::id($_POST['fk_proveedor'] ?? null, 'proveedor');
        $fk_status    = Validator::id($_POST['fk_status'] ?? null, 'estado');

        if (!$this->model->existeProveedor($fk_proveedor)) {
            echo json_encode(['success' => false, 'error' => 'El proveedor seleccionado no existe']);
            return;
        }
        if (!$this->model->existeStatus($fk_status)) {
            echo json_encode(['success' => false, 'error' => 'El estado seleccionado no existe']);
            return;
        }

        $resultado = $this->model->actualizarOrden($id, $numero, $fecha, $fk_proveedor, $fk_status);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Solicitud actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la solicitud']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la solicitud');
        $resultado = $this->model->eliminarOrden($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Solicitud eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la solicitud']
        );
    }

    private function lineas(): void
    {
        $orden_id = Validator::id($_GET['orden_id'] ?? null, 'ID de la solicitud');
        $lineas = $this->model->obtenerLineas($orden_id);
        $orden = $this->model->obtenerOrdenPorId($orden_id);
        echo json_encode(['success' => true, 'data' => ['lineas' => $lineas, 'orden' => $orden]]);
    }

    private function agregarLinea(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $orden_id    = Validator::id($_POST['orden_id'] ?? null, 'ID de la solicitud');
        $producto_id = Validator::id($_POST['producto_id'] ?? null, 'producto');
        $cantidad    = Validator::entero($_POST['cantidad'] ?? null, 'cantidad', ['required' => true, 'min' => 1, 'max' => 99999]);
        $precio      = Validator::decimal($_POST['precio'] ?? null, 'precio', ['required' => true, 'min' => 0.01]);

        $resultado = $this->model->agregarLinea($orden_id, $producto_id, $cantidad, $precio);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto agregado a la solicitud']
                : ['success' => false, 'error' => 'Error al agregar producto']
        );
    }

    private function eliminarLinea(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la línea');
        $resultado = $this->model->eliminarLinea($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Línea eliminada']
                : ['success' => false, 'error' => 'Error al eliminar línea']
        );
    }

    private function siguienteNumero(): void
    {
        $numero = $this->model->obtenerSiguienteNumeroOrden();
        echo json_encode(['success' => true, 'data' => ['numero' => $numero]]);
    }

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
