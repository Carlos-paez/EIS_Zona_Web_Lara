<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\ProveedorGestion;

class ProveedorGestionController extends BaseController
{
    private ProveedorGestion $model;

    public function __construct()
    {
        $this->model = new ProveedorGestion();
    }

    public function getModel(): ProveedorGestion
    {
        return $this->model;
    }

    public function setModel(ProveedorGestion $model): self
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
            Logger::error($e, 'Proveedores (gestión) - consulta SQL');
            $this->jsonError('Error de base de datos', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Proveedores (gestión)');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function listar(): void
    {
        $proveedores = $this->model->obtenerProveedores();
        $this->jsonSuccess($proveedores);
    }

    private function kpis(): void
    {
        $this->setResult([
            'total' => $this->model->totalProveedores(),
        ]);
        $this->jsonSuccess($this->getResult());
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del proveedor');
        $this->set('id', $id);
        $proveedor = $this->model->obtenerProveedorPorId($id);
        if ($proveedor) {
            $this->jsonSuccess($proveedor);
        } else {
            $this->jsonError('Proveedor no encontrado', null, 404);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $datos = $this->validarCampos([
            'rif'      => ['label' => 'RIF', 'type' => 'rif'],
            'nombre'   => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'email'    => ['label' => 'email', 'type' => 'email', 'required' => false],
            'telefono' => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $rif      = $datos['rif'];
        $nombre   = $datos['nombre'];
        $email    = $datos['email'];
        $telefono = $datos['telefono'];

        $this->set('rif', $rif)
            ->set('nombre', $nombre)
            ->set('email', $email)
            ->set('telefono', $telefono);

        if ($this->model->existeRif($rif)) {
            $this->jsonError('Ya existe un proveedor con ese RIF', null, 400);
            return;
        }

        $resultado = $this->model->crearProveedor($rif, $nombre, $email, $telefono);
        if ($resultado) {
            $this->jsonSuccess(null, 'Proveedor creado exitosamente');
        } else {
            $this->jsonError('Error al crear el proveedor', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $datos = $this->validarCampos([
            'id'       => ['label' => 'ID del proveedor', 'type' => 'id'],
            'rif'      => ['label' => 'RIF', 'type' => 'rif'],
            'nombre'   => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'email'    => ['label' => 'email', 'type' => 'email', 'required' => false],
            'telefono' => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $id       = $datos['id'];
        $rif      = $datos['rif'];
        $nombre   = $datos['nombre'];
        $email    = $datos['email'];
        $telefono = $datos['telefono'];

        $this->set('id', $id)
            ->set('rif', $rif)
            ->set('nombre', $nombre)
            ->set('email', $email)
            ->set('telefono', $telefono);

        if ($this->model->existeRif($rif, $id)) {
            $this->jsonError('Ya existe otro proveedor con ese RIF', null, 400);
            return;
        }

        $resultado = $this->model->actualizarProveedor($id, $rif, $nombre, $email, $telefono);
        if ($resultado) {
            $this->jsonSuccess(null, 'Proveedor actualizado exitosamente');
        } else {
            $this->jsonError('Error al actualizar el proveedor', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del proveedor');
        $this->set('id', $id);
        $resultado = $this->model->eliminarProveedor($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Proveedor eliminado exitosamente');
        } else {
            $this->jsonError('Error al eliminar el proveedor', null, 400);
        }
    }
}
