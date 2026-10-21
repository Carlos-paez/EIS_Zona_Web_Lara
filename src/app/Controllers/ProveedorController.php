<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Proveedor;

class ProveedorController extends BaseController
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

    public function setModel(Proveedor $model): self
    {
        $this->model = $model;
        return $this;
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
                default          => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Proveedores (solicitudes) - consulta SQL');
            $this->jsonError('Error de base de datos', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Proveedores (solicitudes)');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function listar(): void
    {
        $ordenes = $this->model->obtenerOrdenes();
        $this->jsonSuccess($ordenes);
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
        $this->setResult([
            'total'      => $this->model->totalSolicitudes(),
            'pendientes' => $pendientes,
            'recibidas'  => $recibidas,
        ]);
        $this->jsonSuccess($this->getResult());
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID de la solicitud');
        $this->set('id', $id);
        $orden = $this->model->obtenerOrdenPorId($id);
        if ($orden) {
            $this->jsonSuccess($orden);
        } else {
            $this->jsonError('Solicitud no encontrada', null, 404);
        }
    }

    private function productos(): void
    {
        $productos = $this->model->obtenerProductos();
        $this->jsonSuccess($productos);
    }

    private function statuses(): void
    {
        $statuses = $this->model->obtenerStatuses();
        $this->jsonSuccess($statuses);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'numero'       => ['label' => 'número de orden', 'type' => 'numeroOrden'],
            'fecha'        => ['label' => 'fecha', 'type' => 'fecha', 'required' => true],
            'fk_proveedor' => ['label' => 'proveedor', 'type' => 'id'],
            'fk_status'    => ['label' => 'estado', 'type' => 'id'],
        ]);

        $numero       = $datos['numero'];
        $fecha        = $datos['fecha'];
        $fk_proveedor = $datos['fk_proveedor'];
        $fk_status    = $datos['fk_status'];

        $this->set('numero', $numero)
            ->set('fecha', $fecha)
            ->set('fk_proveedor', $fk_proveedor)
            ->set('fk_status', $fk_status);

        if (!$this->model->existeProveedor($fk_proveedor)) {
            $this->jsonError('El proveedor seleccionado no existe', null, 400);
            return;
        }
        if (!$this->model->existeStatus($fk_status)) {
            $this->jsonError('El estado seleccionado no existe', null, 400);
            return;
        }

        $resultado = $this->model->crearOrden($numero, $fecha, $fk_proveedor, $fk_status);
        if ($resultado) {
            $this->jsonSuccess(['id' => $resultado], 'Solicitud creada exitosamente');
        } else {
            $this->jsonError('Error al crear la solicitud', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'id'           => ['label' => 'ID de la solicitud', 'type' => 'id'],
            'numero'       => ['label' => 'número de orden', 'type' => 'numeroOrden'],
            'fecha'        => ['label' => 'fecha', 'type' => 'fecha', 'required' => true],
            'fk_proveedor' => ['label' => 'proveedor', 'type' => 'id'],
            'fk_status'    => ['label' => 'estado', 'type' => 'id'],
        ]);

        $id           = $datos['id'];
        $numero       = $datos['numero'];
        $fecha        = $datos['fecha'];
        $fk_proveedor = $datos['fk_proveedor'];
        $fk_status    = $datos['fk_status'];

        $this->set('id', $id)
            ->set('numero', $numero)
            ->set('fecha', $fecha)
            ->set('fk_proveedor', $fk_proveedor)
            ->set('fk_status', $fk_status);

        if (!$this->model->existeProveedor($fk_proveedor)) {
            $this->jsonError('El proveedor seleccionado no existe', null, 400);
            return;
        }
        if (!$this->model->existeStatus($fk_status)) {
            $this->jsonError('El estado seleccionado no existe', null, 400);
            return;
        }

        $resultado = $this->model->actualizarOrden($id, $numero, $fecha, $fk_proveedor, $fk_status);
        if ($resultado) {
            $this->jsonSuccess(null, 'Solicitud actualizada exitosamente');
        } else {
            $this->jsonError('Error al actualizar la solicitud', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la solicitud');
        $resultado = $this->model->eliminarOrden($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Solicitud eliminada exitosamente');
        } else {
            $this->jsonError('Error al eliminar la solicitud', null, 400);
        }
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
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'orden_id'    => ['label' => 'ID de la solicitud', 'type' => 'id'],
            'producto_id' => ['label' => 'producto', 'type' => 'id'],
            'cantidad'    => ['label' => 'cantidad', 'type' => 'entero', 'required' => true, 'min' => 1, 'max' => 99999],
            'precio'      => ['label' => 'precio', 'type' => 'decimal', 'required' => true, 'min' => 0.01],
        ]);

        $orden_id    = $datos['orden_id'];
        $producto_id = $datos['producto_id'];
        $cantidad    = $datos['cantidad'];
        $precio      = $datos['precio'];

        $this->set('orden_id', $orden_id)
            ->set('producto_id', $producto_id)
            ->set('cantidad', $cantidad)
            ->set('precio', $precio);

        $resultado = $this->model->agregarLinea($orden_id, $producto_id, $cantidad, $precio);
        if ($resultado) {
            $this->jsonSuccess(null, 'Producto agregado a la solicitud');
        } else {
            $this->jsonError('Error al agregar producto', null, 400);
        }
    }

    private function eliminarLinea(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la línea');
        $this->set('id', $id);
        $resultado = $this->model->eliminarLinea($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Línea eliminada');
        } else {
            $this->jsonError('Error al eliminar línea', null, 400);
        }
    }

    private function siguienteNumero(): void
    {
        $numero = $this->model->obtenerSiguienteNumeroOrden();
        $this->jsonSuccess(['numero' => $numero]);
    }
}
