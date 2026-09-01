<?php

namespace App\Controllers;

use App\Core\Exporter;
use App\Core\Router;
use App\Models\Reporte;

/**
 * Controlador de Reportes y Estadísticas.
 *
 * Expone endpoints AJAX para consultar KPIs y resultados de un reporte, y
 * genera la descarga de los resultados en CSV, Excel o PDF.
 */
class ReporteController
{
    private Reporte $model;

    public function __construct()
    {
        $this->model = new Reporte();
    }

    public function getModel(): Reporte
    {
        return $this->model;
    }

    public function setModel(Reporte $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'kpis'      => $this->kpis(),
                'consultar' => $this->consultar(),
                'exportar'  => $this->exportar(),
                default     => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\InvalidArgumentException $e) {
            $this->json(false, null, $e->getMessage());
        } catch (\Exception $e) {
            $this->json(false, null, 'Error interno del servidor');
        }
    }

    private function kpis(): void
    {
        $this->json(true, $this->model->kpis());
    }

    private function consultar(): void
    {
        $tipo  = trim($_GET['tipo'] ?? '');
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');
        $this->validarRango($desde, $hasta);

        $resultado = $this->model->consultar($tipo, $desde, $hasta);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $resultado]);
    }

    private function exportar(): void
    {
        if (!Router::verifyCsrfToken($_GET['csrf_token'] ?? null)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $tipo    = trim($_GET['tipo'] ?? '');
        $formato = strtolower(trim($_GET['formato'] ?? ''));
        $desde   = trim($_GET['desde'] ?? '');
        $hasta   = trim($_GET['hasta'] ?? '');
        $this->validarRango($desde, $hasta);

        if (!in_array($formato, ['csv', 'excel', 'pdf'], true)) {
            throw new \InvalidArgumentException('Formato de salida no válido');
        }

        $resultado = $this->model->consultar($tipo, $desde, $hasta);
        $titulo    = $this->model->nombreTipo($tipo);

        match ($formato) {
            'csv'   => Exporter::csv($titulo, $resultado['columnas'], $resultado['filas']),
            'excel' => Exporter::excel($titulo, $resultado['columnas'], $resultado['filas']),
            'pdf'   => Exporter::pdf($titulo, $resultado['columnas'], $resultado['filas']),
        };
    }

    private function validarRango(string $desde, string $hasta): void
    {
        if ($desde === '' || $hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            throw new \InvalidArgumentException('Debe indicar un rango de fechas válido (YYYY-MM-DD)');
        }
        if ($hasta < $desde) {
            throw new \InvalidArgumentException('La fecha inicial no puede ser posterior a la final');
        }
    }

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        header('Content-Type: application/json');
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
