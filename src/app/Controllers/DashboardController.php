<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Models\Dashboard;

/**
 * Controlador del Panel de Control (Dashboard).
 *
 * Proporciona el endpoint AJAX de KPIs reales; la vista se renderiza en
 * servidor a partir del modelo.
 */
class DashboardController extends BaseController
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

    public function setModel(Dashboard $model): self
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
                'kpis'   => $this->kpis(),
                default  => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (\Exception $e) {
            Logger::error($e, 'Dashboard');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function kpis(): void
    {
        $this->jsonSuccess($this->model->kpis());
    }
}
