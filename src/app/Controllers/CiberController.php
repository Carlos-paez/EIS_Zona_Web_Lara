<?php

namespace App\Controllers;

use App\Core\Router;
use App\Models\CiberControl;

class CiberController
{
    private CiberControl $model;

    public function __construct()
    {
        $this->model = new CiberControl();
    }

    public function getModel(): CiberControl
    {
        return $this->model;
    }

    public function setModel(CiberControl $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'estaciones'    => $this->estaciones(),
                'tarifas'       => $this->tarifas(),
                'buscarCliente' => $this->buscarCliente(),
                'iniciar'       => $this->iniciar(),
                'finalizar'     => $this->finalizar(),
                default         => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function estaciones(): void
    {
        $estaciones = $this->model->listarEstaciones();
        echo json_encode(['success' => true, 'data' => $estaciones]);
    }

    private function tarifas(): void
    {
        $tarifas = $this->model->listarTarifas();
        echo json_encode(['success' => true, 'data' => $tarifas]);
    }

    private function buscarCliente(): void
    {
        $cedula = trim($_GET['cedula'] ?? '');
        if ($cedula === '') {
            echo json_encode(['success' => true, 'data' => null]);
            return;
        }
        $cliente = $this->model->buscarCliente($cedula);
        echo json_encode(['success' => true, 'data' => $cliente ?: null]);
    }

    private function iniciar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $ciudadano = trim($_POST['ciudadano'] ?? '');
        $cedula    = trim($_POST['cedula'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $activoId  = (int)($_POST['activo_id'] ?? 0);
        $tarifaId  = (int)($_POST['tarifa_id'] ?? 0);
        $tiempoUso = trim($_POST['tiempo_uso'] ?? '');

        if (empty($ciudadano) || empty($cedula)) {
            echo json_encode(['success' => false, 'error' => 'Nombre y cédula del cliente son obligatorios']);
            return;
        }
        if ($activoId <= 0 || $tarifaId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Estación y tarifa son obligatorias']);
            return;
        }

        $sesion_id = $this->model->iniciarSesion($ciudadano, $cedula, $direccion, $telefono, $activoId, $tarifaId, $tiempoUso);
        echo json_encode(
            $sesion_id
                ? ['success' => true, 'message' => 'Sesión iniciada exitosamente', 'data' => ['sesion_id' => $sesion_id]]
                : ['success' => false, 'error' => 'Error al iniciar la sesión']
        );
    }

    private function finalizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $sesionId = (int)($_POST['sesion_id'] ?? 0);
        if (!$sesionId) {
            echo json_encode(['success' => false, 'error' => 'ID de sesión no válido']);
            return;
        }

        $resultado = $this->model->finalizarSesion($sesionId);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Sesión finalizada exitosamente']
                : ['success' => false, 'error' => 'La sesión ya fue finalizada o no existe']
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
