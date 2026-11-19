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
        $this->setModel(new Dashboard());
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

    /**
     * Todas las métricas del panel se obtienen aquí a través del modelo y se
     * entregan a la vista como datos, nunca desde la vista.
     *
     * @return array<string,mixed>
     */
    public function index(): array
    {
        $model = $this->getModel();

        return [
            'hoy'              => $this->seguro(fn() => $model->ventasHoy(), ['total' => 0, 'transacciones' => 0]),
            'stockCritico'     => $this->seguro(fn() => $model->stockCritico(), 0),
            'sesionesCyber'    => $this->seguro(fn() => $model->sesionesCyberActivas(), 0),
            'solicitudesPend'  => $this->seguro(fn() => $model->solicitudesPendientes(), 0),
            'ventasPorDia'     => $this->seguro(fn() => $model->ventasPorDia(), []),
            'productosStock'   => $this->seguro(fn() => $model->productosStockCritico(), []),
            'actividad'        => $this->seguro(fn() => $model->actividadReciente(), []),
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $this->cargarRequest();

        $action = $this->get('action', '');

        try {
            match ($action) {
                'kpis'   => $this->kpis(),
                default  => $this->jsonError('La acción solicitada no es válida.', null, 400),
            };
        } catch (\Exception $e) {
            Logger::error($e, 'Dashboard');
            $this->jsonError('Ocurrió un error interno. Intenta de nuevo.', null, 500);
        }
    }

    private function kpis(): void
    {
        $this->jsonSuccess($this->getModel()->kpis());
    }
}
