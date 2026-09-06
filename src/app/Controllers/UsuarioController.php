<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Usuario;

class UsuarioController
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function getModel(): Usuario
    {
        return $this->model;
    }

    public function setModel(Usuario $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'       => $this->listar(),
                'detalle'      => $this->detalle(),
                'kpis'         => $this->kpis(),
                'roles'        => $this->roles(),
                'crear'        => $this->crear(),
                'actualizar'   => $this->actualizar(),
                'estado'       => $this->estado(),
                'eliminar'     => $this->eliminar(),
                'password'     => $this->password(),
                default        => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el usuario tiene registros asociados.']);
            } elseif (str_contains($msg, 'Duplicate entry')) {
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya está en uso']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
            }
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function listar(): void
    {
        $usuarios = $this->model->obtenerTodos();
        echo json_encode(['success' => true, 'data' => $usuarios]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del usuario');
        $usuario = $this->model->obtenerPorId($id);
        if ($usuario) {
            $usuario['activo'] = $usuario['estatus_num'] ?? ($usuario['estatus'] === '1' || $usuario['estatus'] === 'activo' ? '1' : '0');
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        }
    }

    private function kpis(): void
    {
        $usuarios = $this->model->obtenerTodos();
        $total = count($usuarios);
        $activos = count(array_filter($usuarios, fn($u) => ($u['activo'] ?? '0') === '1'));
        $adminCount = 0;
        foreach ($usuarios as $u) {
            $rolLower = strtolower($u['rol_nombre'] ?? $u['rol'] ?? '');
            if (str_contains($rolLower, 'admin')) {
                $adminCount++;
            }
        }
        echo json_encode([
            'success' => true,
            'data' => [
                'total'     => $total,
                'activos'   => $activos,
                'inactivos' => $total - $activos,
                'administradores' => $adminCount,
            ]
        ]);
    }

    private function roles(): void
    {
        echo json_encode(['success' => true, 'data' => $this->model->obtenerRolesAsignables()]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $user_name = Validator::username($_POST['user_name'] ?? null, 'nombre de usuario');
        $nombre    = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos']);
        $apellido  = Validator::texto($_POST['apellido'] ?? null, 'apellido', ['required' => false, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El apellido contiene caracteres no permitidos']);
        $email     = Validator::email($_POST['email'] ?? null, 'email');
        $password  = (string)($_POST['password'] ?? '');
        $rol       = Validator::entero($_POST['rol'] ?? null, 'rol', ['required' => false, 'min' => 1]);

        if (mb_strlen($password) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }
        if ($this->model->existeUsername($user_name)) {
            throw new \InvalidArgumentException('Ya existe un usuario con ese nombre de usuario');
        }

        $resultado = $this->model->crear($user_name, $password, $nombre, $apellido, $email, $rol > 0 ? $rol : null);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el usuario']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id       = Validator::id($_POST['id'] ?? null, 'ID del usuario');
        $nombre   = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos']);
        $apellido = Validator::texto($_POST['apellido'] ?? null, 'apellido', ['required' => false, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El apellido contiene caracteres no permitidos']);
        $email    = Validator::email($_POST['email'] ?? null, 'email');
        $rol      = Validator::entero($_POST['rol'] ?? null, 'rol', ['required' => false, 'min' => 1]);
        $estatus  = (string)($_POST['estatus'] ?? '1');
        $password = (string)($_POST['password'] ?? '');

        if (!in_array($estatus, ['0', '1'], true)) {
            throw new \InvalidArgumentException('El estado no es válido');
        }
        if ((int)$estatus === 0 && $id === (int)($_SESSION['user_id'] ?? 0)) {
            throw new \InvalidArgumentException('No puedes desactivar tu propio usuario');
        }

        $resultado = $this->model->actualizar($id, $nombre, $apellido, $email, $rol > 0 ? $rol : null, $estatus);

        if ($resultado && $password !== '') {
            try {
                $resultado = $this->model->actualizarPassword($id, $password);
            } catch (\InvalidArgumentException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                return;
            }
        }

        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el usuario']
        );
    }

    private function estado(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id     = Validator::id($_POST['id'] ?? null, 'ID del usuario');
        $activo = Validator::bool($_POST['activo'] ?? null, 'estado');

        if ($activo === 0 && $id === (int)($_SESSION['user_id'] ?? 0)) {
            throw new \InvalidArgumentException('No puedes desactivar tu propio usuario');
        }

        $resultado = $this->model->cambiarEstado($id, $activo === 1);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activo ? 'Usuario activado' : 'Usuario desactivado']
                : ['success' => false, 'error' => 'Error al cambiar el estado']
        );
    }

    private function password(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id       = Validator::id($_POST['id'] ?? null, 'ID del usuario');
        $password = (string)($_POST['password'] ?? '');

        $resultado = $this->model->actualizarPassword($id, $password);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Contraseña actualizada']
                : ['success' => false, 'error' => 'Error al actualizar la contraseña']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del usuario');
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propio usuario']);
            return;
        }

        $resultado = $this->model->eliminar($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario eliminado exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar el usuario']
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