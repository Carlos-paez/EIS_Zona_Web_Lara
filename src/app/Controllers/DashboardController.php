<?php

namespace App\Controllers;

use App\Models\Dashboard;

/**
 * Controlador del Panel de Control (Dashboard).
 *
 * Proporciona el endpoint AJAX de KPIs reales; la vista se renderiza en
 * servidor a partir del modelo.
 */
class DashboardController
{
    private Dashboard $model;

    public function __construct()
    {
        $this->model = new Dashboard();
    }

    public function getModel(): Dashboard
    {
        return $this->model;
    }

    public function setModel(Dashboard $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'kpis'   => $this->kpis(),
                default  => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function kpis(): void
    {
        echo json_encode(['success' => true, 'data' => $this->model->kpis()]);
    }

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
