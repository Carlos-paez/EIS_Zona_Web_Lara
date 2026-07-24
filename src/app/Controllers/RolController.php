<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\Rol;

class RolController
{
    private Rol $model;

    public function __construct()
    {
        $this->model = new Rol();
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
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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

        $nombre_rol = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (empty($nombre_rol)) {
            echo json_encode(['success' => false, 'error' => 'El nombre del rol es obligatorio']);
            return;
        }
        $resultado = $this->model->crearRol($nombre_rol);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Rol creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el rol (posible nombre duplicado)']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nombre_rol = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        if (!$id || empty($nombre_rol)) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }
        $resultado = $this->model->actualizarRol($id, $nombre_rol);
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

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
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
        $rol_id = (int)($_GET['rol_id'] ?? 0);
        if (!$rol_id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no válido']);
            return;
        }
        $permisos = $this->model->obtenerPermisosPorRol($rol_id);
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    private function guardarPermisos(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $rol_id = (int)($_POST['rol_id'] ?? 0);
        $permiso_ids = isset($_POST['permisos']) ? array_map('intval', (array)$_POST['permisos']) : [];

        if (!$rol_id) {
            echo json_encode(['success' => false, 'error' => 'ID de rol no válido']);
            return;
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

        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        $rol_id = (int)($_POST['rol_id'] ?? 0);
        if (!$usuario_id || !$rol_id) {
            echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
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
