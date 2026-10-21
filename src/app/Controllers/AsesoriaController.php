<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Asesoria;

class AsesoriaController
{
    private Asesoria $model;

    public function __construct()
    {
        $this->model = new Asesoria();
    }

    public function getModel(): Asesoria
    {
        return $this->model;
    }

    public function setModel(Asesoria $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'buscar'     => $this->buscar(),
                'crear'      => $this->crear(),
                'kpis'       => $this->kpis(),
                default      => $this->json(false, null, 'Acción no válida'),
            };
        } catch (ValidationException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'fieldErrors' => $e->errores()]);
        } catch (\PDOException $e) {
            Logger::error($e, 'Asesorías - consulta SQL');
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Asesorías');
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function listar(): void
    {
        $asesorias = $this->model->obtenerTodas();
        echo json_encode(['success' => true, 'data' => $asesorias]);
    }

    private function kpis(): void
    {
        $total = $this->model->obtenerTodas();
        $porEstado = $this->model->contarPorEstado();

        $perm = 0;
        $den  = 0;
        foreach ($porEstado as $fila) {
            if (($fila['estado'] ?? '') === 'Permitido') {
                $perm = (int)$fila['total'];
            } elseif (($fila['estado'] ?? '') === 'Denegado') {
                $den = (int)$fila['total'];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'total'     => count($total),
                'permitidas' => $perm,
                'derivadas' => $den,
            ]
        ]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID de la asesoría');
        $asesoria = $this->model->obtenerPorId($id);
        if ($asesoria) {
            echo json_encode(['success' => true, 'data' => $asesoria]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Asesoría no encontrada']);
        }
    }

    private function buscar(): void
    {
        $cedula = Validator::busqueda($_GET['cedula'] ?? '', 'cédula');
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }
        $resultados = $this->model->buscarPorCedula($cedula);
        echo json_encode(['success' => true, 'data' => $resultados]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $datos = Validator::validar($_POST, [
            'ciudadano'   => ['label' => 'ciudadano', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 200, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El ciudadano contiene caracteres no permitidos'],
            'cedula'      => ['label' => 'cédula', 'type' => 'cedula'],
            'documento'   => ['label' => 'tipo de documento', 'type' => 'texto', 'required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos'],
            'descripcion' => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000],
            'direccion'   => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500],
            'telefono'    => ['label' => 'teléfono', 'type' => 'telefono'],
        ]);

        $ciudadano   = $datos['ciudadano'];
        $cedula      = $datos['cedula'];
        $documento   = $datos['documento'];
        $descripcion = $datos['descripcion'];
        $direccion   = $datos['direccion'];
        $telefono    = $datos['telefono'];

        $resultado = $this->model->crear($ciudadano, $cedula, $documento, $descripcion, $direccion, $telefono);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Asesoría registrada exitosamente']
                : ['success' => false, 'error' => 'Error al registrar la asesoría']
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
