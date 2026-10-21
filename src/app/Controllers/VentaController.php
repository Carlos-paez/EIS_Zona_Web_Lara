<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Cliente;
use App\Models\Venta;

class VentaController extends BaseController
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

    public function setModel(Venta $model): self
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
                'productos'     => $this->productos(),
                'clientes'      => $this->clientes(),
                'buscarCliente' => $this->buscarCliente(),
                'registrar'     => $this->registrar(),
                default         => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Ventas - consulta SQL');
            $this->jsonError('Error de base de datos', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            Logger::error($e, 'Ventas');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function productos(): void
    {
        $productos = $this->model->listarProductos();
        $this->jsonSuccess($productos);
    }

    private function clientes(): void
    {
        $clientes = (new Cliente())->obtenerClientes();
        $this->jsonSuccess($clientes);
    }

    private function buscarCliente(): void
    {
        $cedula = Validator::texto($_GET['cedula'] ?? '', 'cédula', ['required' => false, 'max' => 20]);
        $this->set('cedula', $cedula);
        if ($cedula === '') {
            $this->jsonSuccess(null);
            return;
        }
        $cliente = (new Cliente())->obtenerClientePorCedula($cedula);
        $this->jsonSuccess($cliente ?: null);
    }

    private function registrar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'ciudadano' => ['label' => 'nombre del cliente', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => Validator::MAX_NOMBRE, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre del cliente contiene caracteres no permitidos'],
            'cedula'    => ['label' => 'cédula', 'type' => 'cedula'],
            'direccion' => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => Validator::MAX_DIRECCION],
            'telefono'  => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $ciudadano = $datos['ciudadano'];
        $cedula    = $datos['cedula'];
        $direccion = $datos['direccion'];
        $telefono  = $datos['telefono'];
        try {
            $items = Validator::itemsVenta($_POST['items'] ?? '');
        } catch (\InvalidArgumentException $e) {
            throw new ValidationException(['items' => $e->getMessage()]);
        }

        $this->set('ciudadano', $ciudadano)
            ->set('cedula', $cedula)
            ->set('direccion', $direccion)
            ->set('telefono', $telefono)
            ->set('items', $items);

        $usuarioId = (int)($_SESSION['user_id'] ?? 0);

        $orden_id = $this->model->registrarVenta($items, $ciudadano, $cedula, $direccion, $telefono, $usuarioId);
        if ($orden_id) {
            $this->jsonSuccess(['orden_id' => $orden_id], 'Venta registrada exitosamente');
        } else {
            $this->jsonError('Error al registrar la venta', null, 400);
        }
    }

    // json heredado desde BaseController
}
