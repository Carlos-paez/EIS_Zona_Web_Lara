<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Rol;

class RolController
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

    public function setModel(Rol $model): void
    {
        $this->model = $model;
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
                default           => $this->json(false, null, 'Acción no válida'),
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
        $roles = $this->model->listarRoles();
        echo json_encode(['success' => true, 'data' => $roles]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del rol');
        $rol = $this->model->obtenerRolPorId($id);
        if ($rol) {
            echo json_encode(['success' => true, 'data' => $rol]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $nombre_rol = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        if (empty($nombre_rol)) {
            echo json_encode(['success' => false, 'error' => 'El nombre del rol es obligatorio']);
            return;
        }

        if ($this->model->existeNombreRol($nombre_rol)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe un rol con ese nombre']);
            return;
        }

        $resultado = $this->model->crearRol($nombre_rol, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Rol creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el rol']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del rol');
        $nombre_rol = Validator::texto($_POST['nombre'] ?? null, 'nombre de rol', ['required' => true, 'min' => 2, 'max' => 50]);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 500]);

        if ($this->model->existeNombreRol($nombre_rol, $id)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe otro rol con ese nombre']);
            return;
        }

        $resultado = $this->model->actualizarRol($id, $nombre_rol, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Rol actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el rol']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del rol');
        if ($id === 1) {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar el rol de Administrador']);
            return;
        }
        $resultado = $this->model->eliminarRol($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Rol eliminado exitosamente']
                : ['success' => false, 'error' => 'No se puede eliminar el rol porque tiene usuarios asignados']
        );
    }

    private function permisos(): void
    {
        $permisos = $this->model->obtenerPermisos();
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    private function permisosRol(): void
    {
        $rol_id = Validator::id($_GET['rol_id'] ?? null, 'ID del rol');
        $permisos = $this->model->obtenerPermisosPorRol($rol_id);
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    private function guardarPermisos(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $rol_id = Validator::id($_POST['rol_id'] ?? null, 'ID del rol');
        $permiso_ids = isset($_POST['permisos']) ? array_values((array)$_POST['permisos']) : [];
        foreach ($permiso_ids as $p) {
            Validator::entero($p, 'permiso', ['required' => true, 'min' => 1]);
        }

        $resultado = $this->model->guardarPermisosRol($rol_id, $permiso_ids);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Permisos guardados exitosamente']
                : ['success' => false, 'error' => 'Error al guardar permisos']
        );
    }

    private function usuarios(): void
    {
        $usuarios = $this->model->obtenerUsuarios();
        $roles = $this->model->obtenerRoles();
        echo json_encode([
            'success' => true,
            'data' => [
                'usuarios' => $usuarios,
                'roles'    => $roles,
            ]
        ]);
    }

    private function asignarRol(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $usuario_id = Validator::id($_POST['usuario_id'] ?? null, 'ID del usuario');
        $rol_id = Validator::id($_POST['rol_id'] ?? null, 'ID del rol');
        if ($rol_id === 1) {
            echo json_encode(['success' => false, 'error' => 'No se puede asignar el rol de Administrador directamente']);
            return;
        }
        $resultado = $this->model->asignarRolAUsuario($usuario_id, $rol_id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Rol asignado exitosamente']
                : ['success' => false, 'error' => 'Error al asignar rol']
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
