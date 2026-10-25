<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Cliente;

class ClienteController extends BaseController
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

    public function setModel(Cliente $model): self
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
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'crear'      => $this->crear(),
                'actualizar' => $this->actualizar(),
                'eliminar'   => $this->eliminar(),
                'kpis'       => $this->kpis(),
                default      => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Clientes - consulta SQL');
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                $this->jsonError('No se puede eliminar: el cliente tiene registros asociados.', null, 400);
            } else {
                $this->jsonError('Error de base de datos', null, 500);
            }
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Clientes');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function listar(): void
    {
        $clientes = $this->model->obtenerClientes();
        $this->jsonSuccess($clientes);
    }

    private function kpis(): void
    {
        $this->setResult([
            'total' => $this->model->totalClientes(),
        ]);
        $this->jsonSuccess($this->getResult());
    }

    private function detalle(): void
    {
        $this->hydrateFromGet(['id' => 'int']);
        $id = $this->get('id', 0) > 0 ? (int)$this->get('id') : Validator::id($_GET['id'] ?? null, 'ID del cliente');
        $cliente = $this->model->obtenerClientePorId($id);
        if ($cliente) {
            $this->jsonSuccess($cliente);
        } else {
            $this->jsonError('Cliente no encontrado', null, 404);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'cedula'    => ['label' => 'cédula', 'type' => 'cedula'],
            'nombre'    => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'apellido'  => ['label' => 'apellido', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El apellido contiene caracteres no permitidos'],
            'direccion' => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.direccion'), 'patternMessage' => 'La dirección contiene caracteres no permitidos'],
            'telefono'  => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $cedula    = $datos['cedula'];
        $nombre    = $datos['nombre'];
        $apellido  = $datos['apellido'];
        $direccion = $datos['direccion'];
        $telefono  = $datos['telefono'];

        $this->set('cedula', $cedula)
            ->set('nombre', $nombre)
            ->set('apellido', $apellido)
            ->set('direccion', $direccion)
            ->set('telefono', $telefono);

        if ($this->model->existeCedula($cedula)) {
            $this->jsonError('Ya existe un cliente con esa cédula', null, 400);
            return;
        }

        $resultado = $this->model->crearCliente($cedula, $nombre, $apellido, $direccion, $telefono);
        if ($resultado) {
            $this->jsonSuccess(null, 'Cliente creado exitosamente');
        } else {
            $this->jsonError('Error al crear el cliente', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'id'        => ['label' => 'ID del cliente', 'type' => 'id'],
            'cedula'    => ['label' => 'cédula', 'type' => 'cedula'],
            'nombre'    => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'apellido'  => ['label' => 'apellido', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El apellido contiene caracteres no permitidos'],
            'direccion' => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.direccion'), 'patternMessage' => 'La dirección contiene caracteres no permitidos'],
            'telefono'  => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $id        = $datos['id'];
        $cedula    = $datos['cedula'];
        $nombre    = $datos['nombre'];
        $apellido  = $datos['apellido'];
        $direccion = $datos['direccion'];
        $telefono  = $datos['telefono'];

        $this->set('id', $id)
            ->set('cedula', $cedula)
            ->set('nombre', $nombre)
            ->set('apellido', $apellido)
            ->set('direccion', $direccion)
            ->set('telefono', $telefono);

        if ($this->model->existeCedula($cedula, $id)) {
            $this->jsonError('Ya existe otro cliente con esa cédula', null, 400);
            return;
        }

        $resultado = $this->model->actualizarCliente($id, $cedula, $nombre, $apellido, $direccion, $telefono);
        if ($resultado) {
            $this->jsonSuccess(null, 'Cliente actualizado exitosamente');
        } else {
            $this->jsonError('Error al actualizar el cliente', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del cliente');
        $this->set('id', $id);
        $resultado = $this->model->eliminarCliente($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Cliente eliminado exitosamente');
        } else {
            $this->jsonError('Error al eliminar el cliente', null, 400);
        }
    }

    // json helper heredado desde BaseController
}
