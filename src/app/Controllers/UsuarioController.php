<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Usuario;

class UsuarioController extends BaseController
{
    private Usuario $model;

    public function __construct()
    {
        $this->setModel(new Usuario());
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

        $this->cargarRequest();

        $action = $this->get('action', '');

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
                default        => $this->json(false, null, 'La acción solicitada no es válida.'),
            };
        } catch (ValidationException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'fieldErrors' => $e->errores()]);
        } catch (\PDOException $e) {
            Logger::error($e, 'Usuarios - consulta SQL');
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar el usuario porque tiene registros asociados.']);
            } elseif (str_contains($msg, 'Duplicate entry')) {
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya está en uso.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo conectar con la base de datos. Intenta de nuevo.']);
            }
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Usuarios');
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error interno. Intenta de nuevo.']);
        }
    }

    private function listar(): void
    {
        $usuarios = $this->getModel()->obtenerTodos();
        echo json_encode(['success' => true, 'data' => $usuarios]);
    }

    private function detalle(): void
    {
        $id = Validator::id($this->get('id'), 'ID del usuario');
        $this->set('id', $id);
        $usuario = $this->getModel()->obtenerPorId($id);
        if ($usuario) {
            $usuario['activo'] = $usuario['estatus_num'] ?? ($usuario['estatus'] === '1' || $usuario['estatus'] === 'activo' ? '1' : '0');
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se encontró el usuario.']);
        }
    }

    /**
     * Calcula los KPIs de usuarios una sola vez: lo usan tanto el endpoint
     * AJAX como los datos iniciales de la vista.
     *
     * @return array<string,int>
     */
    private function calcularKpis(): array
    {
        $usuarios = $this->seguro(fn() => $this->getModel()->obtenerTodos(), []);
        $total    = count($usuarios);
        $activos  = count(array_filter($usuarios, fn($u) => ($u['activo'] ?? '0') === '1'));
        $adminCount = 0;

        foreach ($usuarios as $u) {
            $rolLower = strtolower($u['rol_nombre'] ?? $u['rol'] ?? '');
            if (str_contains($rolLower, 'admin')) {
                $adminCount++;
            }
        }

        return [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $total - $activos,
            'administradores' => $adminCount,
        ];
    }

    private function kpis(): void
    {
        echo json_encode(['success' => true, 'data' => $this->calcularKpis()]);
    }

    /**
     * @return array<string,mixed>
     */
    public function index(): array
    {
        $kpis = $this->calcularKpis();

        return [
            'totalUsuarios' => $kpis['total'],
            'activos'       => $kpis['activos'],
            'inactivos'     => $kpis['inactivos'],
            'adminCount'    => $kpis['administradores'],
        ];
    }

    private function roles(): void
    {
        echo json_encode(['success' => true, 'data' => $this->getModel()->obtenerRolesAsignables()]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'user_name' => ['label' => 'nombre de usuario', 'type' => 'username'],
            'nombre'    => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'apellido'  => ['label' => 'apellido', 'type' => 'texto', 'required' => false, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El apellido contiene caracteres no permitidos'],
            'email'     => ['label' => 'email', 'type' => 'email', 'required' => true],
            'password'  => ['label' => 'contraseña', 'type' => 'password', 'required' => true, 'min' => 8, 'complejidad' => true],
            'rol'       => ['label' => 'rol', 'type' => 'entero', 'required' => false, 'min' => 1],
        ]);

        $user_name = $datos['user_name'];
        $nombre    = $datos['nombre'];
        $apellido  = $datos['apellido'];
        $email     = $datos['email'];
        $password  = $datos['password'];
        $rol       = $datos['rol'];

        $this->set('user_name', $user_name)
            ->set('nombre', $nombre)
            ->set('apellido', $apellido)
            ->set('email', $email)
            ->set('password', $password)
            ->set('rol', $rol);

        if ($this->getModel()->existeUsername($user_name)) {
            throw new ValidationException(['user_name' => 'Ya existe un usuario con ese nombre de usuario']);
        }

        $resultado = $this->getModel()->crear($user_name, $password, $nombre, $apellido, $email, $rol > 0 ? $rol : null);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario creado exitosamente']
                : ['success' => false, 'error' => 'No se pudo crear el usuario. Intenta de nuevo.']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'id'       => ['label' => 'ID del usuario', 'type' => 'id'],
            'nombre'   => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
            'apellido' => ['label' => 'apellido', 'type' => 'texto', 'required' => false, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El apellido contiene caracteres no permitidos'],
            'email'    => ['label' => 'email', 'type' => 'email', 'required' => true],
            'rol'      => ['label' => 'rol', 'type' => 'entero', 'required' => false, 'min' => 1],
            'estatus'  => ['label' => 'estado', 'type' => 'enum', 'allowed' => ['0', '1'], 'required' => true],
            'password' => ['label' => 'contraseña', 'type' => 'password', 'required' => false, 'min' => 8, 'complejidad' => true],
        ]);

        $id       = $datos['id'];
        $nombre   = $datos['nombre'];
        $apellido = $datos['apellido'];
        $email    = $datos['email'];
        $rol      = $datos['rol'];
        $estatus  = $datos['estatus'];
        $password = $datos['password'];

        $this->set('id', $id)
            ->set('nombre', $nombre)
            ->set('apellido', $apellido)
            ->set('email', $email)
            ->set('rol', $rol)
            ->set('estatus', $estatus)
            ->set('password', $password);

        if ((int)$estatus === 0 && $id === (int)($_SESSION['user_id'] ?? 0)) {
            throw new \InvalidArgumentException('No puedes desactivar tu propio usuario.');
        }

        $resultado = $this->getModel()->actualizar($id, $nombre, $apellido, $email, $rol > 0 ? $rol : null, $estatus);

        if ($resultado && $password !== '') {
            try {
                $resultado = $this->getModel()->actualizarPassword($id, $password);
            } catch (\InvalidArgumentException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                return;
            }
        }

        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario actualizado exitosamente']
                : ['success' => false, 'error' => 'No se pudo actualizar el usuario. Intenta de nuevo.']
        );
    }

    private function estado(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'id'     => ['label' => 'ID del usuario', 'type' => 'id'],
            'activo' => ['label' => 'estado', 'type' => 'bool', 'required' => true],
        ]);

        $id     = $datos['id'];
        $activo = $datos['activo'];

        $this->set('id', $id)->set('activo', $activo);

        if ($activo === 0 && $id === (int)($_SESSION['user_id'] ?? 0)) {
            throw new \InvalidArgumentException('No puedes desactivar tu propio usuario.');
        }

        $resultado = $this->getModel()->cambiarEstado($id, $activo === 1);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => $activo ? 'Usuario activado' : 'Usuario desactivado']
                : ['success' => false, 'error' => 'No se pudo cambiar el estado. Intenta de nuevo.']
        );
    }

    private function password(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $datos = $this->validarCampos([
            'id'       => ['label' => 'ID del usuario', 'type' => 'id'],
            'password' => ['label' => 'contraseña', 'type' => 'password', 'required' => true, 'min' => 8, 'complejidad' => true],
        ]);

        $id       = $datos['id'];
        $password = $datos['password'];

        $this->set('id', $id)->set('password', $password);

        $resultado = $this->getModel()->actualizarPassword($id, $password);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Contraseña actualizada']
                : ['success' => false, 'error' => 'No se pudo actualizar la contraseña. Intenta de nuevo.']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($this->get('csrf_token'))) {
            echo json_encode(['success' => false, 'error' => 'La sesión o el formulario expiró. Recarga la página e intenta de nuevo.']);
            return;
        }

        $id = Validator::id($this->get('id'), 'ID del usuario');
        $this->set('id', $id);
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propio usuario.']);
            return;
        }

        $resultado = $this->getModel()->eliminar($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Usuario eliminado exitosamente']
                : ['success' => false, 'error' => 'No se pudo eliminar el usuario. Intenta de nuevo.']
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