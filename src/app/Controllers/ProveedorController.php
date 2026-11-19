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
        $this->setModel(new Proveedor());
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

        $this->cargarRequest();

        $action = $this->get('action', '');

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
                default          => $this->jsonError('La acción solicitada no es válida.', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Proveedores (solicitudes) - consulta SQL');
            $this->jsonError('No se pudo conectar con la base de datos. Intenta de nuevo.', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Proveedores (solicitudes)');
            $this->jsonError('Ocurrió un error interno. Intenta de nuevo.', null, 500);
        }
    }

    private function listar(): void
    {
        $ordenes = $this->getModel()->obtenerOrdenes();
        $this->jsonSuccess($ordenes);
    }

    /**
     * Calcula los KPIs de solicitudes. Se usa tanto en la respuesta AJAX
     * como en los datos iniciales de la vista, para que ambas rutas apliquen
     * exactamente la misma lógica de negocio.
     *
     * @return array<string,int>
     */
    private function calcularKpis(): array
    {
        $model      = $this->getModel();
        $porEstado  = $this->seguro(fn() => $model->contarPorEstado(), []);
        $pendientes = 0;
        $recibidas  = 0;

        foreach ($porEstado as $row) {
            $est = strtolower($row['estado']);
            if (str_contains($est, 'pend')) {
                $pendientes = (int)$row['total'];
            } elseif (str_contains($est, 'recib')) {
                $recibidas = (int)$row['total'];
            }
        }

        return [
            'total'      => $this->seguro(fn() => $model->totalSolicitudes(), 0),
            'pendientes' => $pendientes,
            'recibidas'  => $recibidas,
        ];
    }

    private function kpis(): void
    {
        $this->setResult($this->calcularKpis());
        $this->jsonSuccess($this->getResult());
    }

    /**
     * @return array<string,mixed>
     */
    public function index(): array
    {
        $model = $this->getModel();
        $kpis  = $this->calcularKpis();

        return [
            'ordenes'     => $this->seguro(fn() => $model->obtenerOrdenes(), []),
            'kpis'        => $this->seguro(fn() => $model->contarPorEstado(), []),
            'total'       => $kpis['total'],
            'proveedores' => $this->seguro(fn() => $model->obtenerProveedores(), []),
            'statuses'    => $this->seguro(fn() => $model->obtenerStatuses(), []),
            'productos'   => $this->seguro(fn() => $model->obtenerProductos(), []),
            'pendientes'  => $kpis['pendientes'],
            'recibidas'   => $kpis['recibidas'],
        ];
    }

    private function detalle(): void
    {
        $id = Validator::id($this->get('id'), 'ID de la solicitud');
        $this->set('id', $id);
        $orden = $this->getModel()->obtenerOrdenPorId($id);
        if ($orden) {
            $this->jsonSuccess($orden);
        } else {
            $this->jsonError('No se encontró la solicitud.', null, 404);
        }
    }

    private function productos(): void
    {
        $productos = $this->getModel()->obtenerProductos();
        $this->jsonSuccess($productos);
    }

    private function statuses(): void
    {
        $statuses = $this->getModel()->obtenerStatuses();
        $this->jsonSuccess($statuses);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
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

        if (!$this->getModel()->existeProveedor($fk_proveedor)) {
            $this->jsonError('El proveedor seleccionado no existe.', null, 400);
            return;
        }
        if (!$this->getModel()->existeStatus($fk_status)) {
            $this->jsonError('El estado seleccionado no existe.', null, 400);
            return;
        }

        $resultado = $this->getModel()->crearOrden($numero, $fecha, $fk_proveedor, $fk_status);
        if ($resultado) {
            $this->jsonSuccess(['id' => $resultado], 'Solicitud creada exitosamente');
        } else {
            $this->jsonError('No se pudo crear la solicitud. Intenta de nuevo.', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
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

        if (!$this->getModel()->existeProveedor($fk_proveedor)) {
            $this->jsonError('El proveedor seleccionado no existe.', null, 400);
            return;
        }
        if (!$this->getModel()->existeStatus($fk_status)) {
            $this->jsonError('El estado seleccionado no existe.', null, 400);
            return;
        }

        $resultado = $this->getModel()->actualizarOrden($id, $numero, $fecha, $fk_proveedor, $fk_status);
        if ($resultado) {
            $this->jsonSuccess(null, 'Solicitud actualizada exitosamente');
        } else {
            $this->jsonError('No se pudo actualizar la solicitud. Intenta de nuevo.', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
            return;
        }

        $id = Validator::id($this->get('id'), 'ID de la solicitud');
        $resultado = $this->getModel()->eliminarOrden($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Solicitud eliminada exitosamente');
        } else {
            $this->jsonError('No se pudo eliminar la solicitud. Intenta de nuevo.', null, 400);
        }
    }

    private function lineas(): void
    {
        $orden_id = Validator::id($this->get('orden_id'), 'ID de la solicitud');
        $lineas = $this->getModel()->obtenerLineas($orden_id);
        $orden = $this->getModel()->obtenerOrdenPorId($orden_id);
        echo json_encode(['success' => true, 'data' => ['lineas' => $lineas, 'orden' => $orden]]);
    }

    private function agregarLinea(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
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

        $resultado = $this->getModel()->agregarLinea($orden_id, $producto_id, $cantidad, $precio);
        if ($resultado) {
            $this->jsonSuccess(null, 'Producto agregado a la solicitud');
        } else {
            $this->jsonError('No se pudo agregar el producto. Intenta de nuevo.', null, 400);
        }
    }

    private function eliminarLinea(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
            return;
        }

        $id = Validator::id($this->get('id'), 'ID de la línea');
        $this->set('id', $id);
        $resultado = $this->getModel()->eliminarLinea($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Línea eliminada');
        } else {
            $this->jsonError('No se pudo eliminar la línea. Intenta de nuevo.', null, 400);
        }
    }

    private function siguienteNumero(): void
    {
        $numero = $this->getModel()->obtenerSiguienteNumeroOrden();
        $this->jsonSuccess(['numero' => $numero]);
    }
}
