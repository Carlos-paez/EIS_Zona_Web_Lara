<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Rol;

class RolController extends BaseController
{
    private Rol $model;

    public function __construct()
    {
        $this->model = new Rol();
    }

    public function getModel(): Rol
    {
        return $this->model;
    }

    public function setModel(Rol $model): self
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
                'listar'          => $this->listar(),
                'detalle'         => $this->detalle(),
                'crear'           => $this->crear(),
                'actualizar'      => $this->actualizar(),
                'eliminar'        => $this->eliminar(),
                'permisos'        => $this->permisos(),
                'permisosRol'     => $this->permisosRol(),
                'guardarPermisos' => $this->guardarPermisos(),
                'usuarios'        => $this->usuarios(),
                'asignarRol'      => $this->asignarRol(),
                default           => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Roles - consulta SQL');
            $this->jsonError('Error de base de datos', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Roles');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function listar(): void
    {
        $roles = $this->model->listarRoles();
        $this->jsonSuccess($roles);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del rol');
        $this->set('id', $id);
        $rol = $this->model->obtenerRolPorId($id);
        if ($rol) {
            $this->jsonSuccess($rol);
        } else {
            $this->jsonError('Rol no encontrado', null, 404);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $datos = $this->validarCampos([
            'nombre'      => ['label' => 'nombre de rol', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 50, 'pattern' => Validator::patron('aplicacion.nombre_rol'), 'patternMessage' => 'El nombre del rol contiene caracteres no permitidos'],
            'descripcion' => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
        ]);
        $nombre_rol  = $datos['nombre'];
        $descripcion = $datos['descripcion'];

        $this->set('nombre_rol', $nombre_rol)->set('descripcion', $descripcion);

        if ($this->model->existeNombreRol($nombre_rol)) {
            $this->jsonError('Ya existe un rol con ese nombre', null, 400);
            return;
        }

        $resultado = $this->model->crearRol($nombre_rol, $descripcion);
        if ($resultado) {
            $this->jsonSuccess(null, 'Rol creado exitosamente');
        } else {
            $this->jsonError('Error al crear el rol', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $datos = $this->validarCampos([
            'id'          => ['label' => 'ID del rol', 'type' => 'id', 'required' => true],
            'nombre'      => ['label' => 'nombre de rol', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 50, 'pattern' => Validator::patron('aplicacion.nombre_rol'), 'patternMessage' => 'El nombre del rol contiene caracteres no permitidos'],
            'descripcion' => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 500, 'pattern' => Validator::patron('aplicacion.descripcion'), 'patternMessage' => 'La descripción contiene caracteres no permitidos'],
        ]);

        $id          = $datos['id'];
        $nombre_rol  = $datos['nombre'];
        $descripcion = $datos['descripcion'];

        $this->set('id', $id)
            ->set('nombre_rol', $nombre_rol)
            ->set('descripcion', $descripcion);

        if ($this->model->existeNombreRol($nombre_rol, $id)) {
            $this->jsonError('Ya existe otro rol con ese nombre', null, 400);
            return;
        }

        $resultado = $this->model->actualizarRol($id, $nombre_rol, $descripcion);
        if ($resultado) {
            $this->jsonSuccess(null, 'Rol actualizado exitosamente');
        } else {
            $this->jsonError('Error al actualizar el rol', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del rol');
        $this->set('id', $id);
        if ($id === 1) {
            $this->jsonError('No se puede eliminar el rol de Administrador', null, 400);
            return;
        }
        $resultado = $this->model->eliminarRol($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Rol eliminado exitosamente');
        } else {
            $this->jsonError('No se puede eliminar el rol porque tiene usuarios asignados', null, 400);
        }
    }

    private function permisos(): void
    {
        $permisos = $this->model->obtenerPermisos();
        $this->jsonSuccess($permisos);
    }

    private function permisosRol(): void
    {
        $rol_id = Validator::id($_GET['rol_id'] ?? null, 'ID del rol');
        $this->set('rol_id', $rol_id);
        $permisos = $this->model->obtenerPermisosPorRol($rol_id);
        $this->jsonSuccess($permisos);
    }

    private function guardarPermisos(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $rol_id = Validator::id($_POST['rol_id'] ?? null, 'ID del rol');
        $permiso_ids = isset($_POST['permisos']) ? array_values((array)$_POST['permisos']) : [];
        foreach ($permiso_ids as $p) {
            Validator::entero($p, 'permiso', ['required' => true, 'min' => 1]);
        }

        $this->set('rol_id', $rol_id)
            ->set('permisos', $permiso_ids);

        $resultado = $this->model->guardarPermisosRol($rol_id, $permiso_ids);
        if ($resultado) {
            $this->jsonSuccess(null, 'Permisos guardados exitosamente');
        } else {
            $this->jsonError('Error al guardar permisos', null, 400);
        }
    }

    private function usuarios(): void
    {
        $usuarios = $this->model->obtenerUsuarios();
        $roles = $this->model->obtenerRoles();
        $this->jsonSuccess([
            'usuarios' => $usuarios,
            'roles'    => $roles,
        ]);
    }

    private function asignarRol(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $usuario_id = Validator::id($_POST['usuario_id'] ?? null, 'ID del usuario');
        $rol_id = Validator::id($_POST['rol_id'] ?? null, 'ID del rol');
        $this->set('usuario_id', $usuario_id)->set('rol_id', $rol_id);
        if ($rol_id === 1) {
            $this->jsonError('No se puede asignar el rol de Administrador directamente', null, 400);
            return;
        }
        $resultado = $this->model->asignarRolAUsuario($usuario_id, $rol_id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Rol asignado exitosamente');
        } else {
            $this->jsonError('Error al asignar rol', null, 400);
        }
    }
}
