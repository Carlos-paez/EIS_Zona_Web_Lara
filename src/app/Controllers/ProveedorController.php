<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\Proveedor;

class ProveedorController
{
    private Proveedor $model;

    public function __construct()
    {
        $this->model = new Proveedor();
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
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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

        $numero       = trim($_POST['numero'] ?? '');
        $fecha        = trim($_POST['fecha'] ?? '');
        $fk_proveedor = (int)($_POST['fk_proveedor'] ?? 0);
        $fk_status    = (int)($_POST['fk_status'] ?? 0);

        if (empty($numero) || empty($fecha) || !$fk_proveedor || !$fk_status) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['success' => false, 'error' => 'Formato de fecha inválido (use YYYY-MM-DD)']);
            return;
        }

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

        $id           = (int)($_POST['id'] ?? 0);
        $numero       = trim($_POST['numero'] ?? '');
        $fecha        = trim($_POST['fecha'] ?? '');
        $fk_proveedor = (int)($_POST['fk_proveedor'] ?? 0);
        $fk_status    = (int)($_POST['fk_status'] ?? 0);

        if (!$id || empty($numero) || empty($fecha) || !$fk_proveedor || !$fk_status) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['success' => false, 'error' => 'Formato de fecha inválido (use YYYY-MM-DD)']);
            return;
        }

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

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $resultado = $this->model->eliminarOrden($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Solicitud eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la solicitud']
        );
    }

    private function lineas(): void
    {
        $orden_id = (int)($_GET['orden_id'] ?? 0);
        if (!$orden_id) {
            echo json_encode(['success' => false, 'error' => 'ID de orden no válido']);
            return;
        }
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

        $orden_id    = (int)($_POST['orden_id'] ?? 0);
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $precio      = (float)($_POST['precio'] ?? 0);

        if (!$orden_id || !$producto_id || $cantidad <= 0 || $precio <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de línea no válidos']);
            return;
        }

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

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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
