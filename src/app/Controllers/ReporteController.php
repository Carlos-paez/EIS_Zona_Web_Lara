<?php

namespace App\Controllers;

use App\Core\Exporter;
use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Reporte;

/**
 * Controlador de Reportes y Estadísticas.
 *
 * Expone endpoints AJAX para consultar KPIs, catálogos de filtros y resultados
 * de un reporte estratégico, y genera la descarga de los resultados en CSV, Excel o PDF.
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
                'filtros'   => $this->filtros(),
                'consultar' => $this->consultar(),
                'exportar'  => $this->exportar(),
                default     => $this->json(false, null, 'Acción no válida'),
            };
        } catch (ValidationException $e) {
            http_response_code(422);
            $this->json(false, ['fieldErrors' => $e->errores()], $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->json(false, null, $e->getMessage());
        } catch (\Exception $e) {
            Logger::error($e, 'Reportes');
            $this->json(false, null, 'Error interno del servidor');
        }
    }

    private function kpis(): void
    {
        $this->json(true, $this->model->kpis());
    }

    private function filtros(): void
    {
        $this->json(true, $this->model->catalogosFiltros());
    }

    private function consultar(): void
    {
        $datos = $this->validarFiltros();

        $tipo = $datos['tipo'];
        $desde = $datos['desde'] ?? date('Y-m-01');
        $hasta = $datos['hasta'] ?? date('Y-m-d');

        if (in_array($tipo, ['inventario', 'stock_bajo', 'cyber'], true)) {
            $desde = $datos['desde'] ?? date('Y-m-01');
            $hasta = $datos['hasta'] ?? date('Y-m-d');
        } else {
            if (($datos['desde'] ?? '') === '' || ($datos['hasta'] ?? '') === '') {
                throw new ValidationException(['desde' => 'Debe seleccionar el rango de fechas', 'hasta' => 'Debe seleccionar el rango de fechas']);
            }
        }

        $filtros = $this->extraerFiltrosAdicionales();
        $resultado = $this->model->consultar($tipo, $desde, $hasta, $filtros);

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

        $datos   = $this->validarFiltros(true);
        $tipo    = $datos['tipo'];
        $formato = $datos['formato'];
        $desde   = $datos['desde'] ?? date('Y-m-01');
        $hasta   = $datos['hasta'] ?? date('Y-m-d');

        $filtros = $this->extraerFiltrosAdicionales();
        $resultado = $this->model->consultar($tipo, $desde, $hasta, $filtros);
        $titulo    = $this->model->nombreTipo($tipo);
        $meta      = ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta];

        match ($formato) {
            'csv'   => Exporter::csv($titulo, $resultado['columnas'], $resultado['filas'], $meta),
            'excel' => Exporter::excel($titulo, $resultado['columnas'], $resultado['filas'], $meta),
            'pdf'   => Exporter::pdf($titulo, $resultado['columnas'], $resultado['filas'], $meta),
            default => throw new \InvalidArgumentException('Formato no válido'),
        };
    }

    /**
     * Extrae y sanea filtros secundarios opcionales (categoría, cliente, proveedor, status).
     */
    private function extraerFiltrosAdicionales(): array
    {
        $filtros = [];
        if (!empty($_GET['categoria_id'])) {
            $filtros['categoria_id'] = (int)$_GET['categoria_id'];
        }
        if (!empty($_GET['proveedor_id'])) {
            $filtros['proveedor_id'] = (int)$_GET['proveedor_id'];
        }
        if (!empty($_GET['cliente_id'])) {
            $filtros['cliente_id'] = (int)$_GET['cliente_id'];
        }
        if (!empty($_GET['status_id'])) {
            $filtros['status_id'] = (int)$_GET['status_id'];
        }
        if (isset($_GET['estado_cyber']) && $_GET['estado_cyber'] !== '') {
            $filtros['estado'] = (int)$_GET['estado_cyber'];
        }
        return $filtros;
    }

    /**
     * Valida los filtros comunes de un reporte (tipo y rango de fechas) y,
     * cuando se solicita, el formato de exportación.
     *
     * @return array{tipo:string,desde?:string,hasta?:string,formato?:string}
     * @throws ValidationException
     */
    private function validarFiltros(bool $conFormato = false): array
    {
        $reglas = [
            'tipo' => ['label' => 'tipo de reporte', 'type' => 'enum', 'allowed' => array_keys(Reporte::TIPOS), 'required' => true],
        ];

        $tipo = $_GET['tipo'] ?? '';
        $requiereFechas = !in_array($tipo, ['inventario', 'stock_bajo', 'cyber'], true);

        if ($requiereFechas) {
            $reglas['desde'] = ['label' => 'fecha inicial', 'type' => 'fecha', 'required' => true];
            $reglas['hasta'] = ['label' => 'fecha final', 'type' => 'fecha', 'required' => true];
        } elseif (isset($_GET['desde']) && $_GET['desde'] !== '') {
            $reglas['desde'] = ['label' => 'fecha inicial', 'type' => 'fecha', 'required' => false];
        }
        if ($requiereFechas) {
            // already added
        } elseif (isset($_GET['hasta']) && $_GET['hasta'] !== '') {
            $reglas['hasta'] = ['label' => 'fecha final', 'type' => 'fecha', 'required' => false];
        }

        if ($conFormato) {
            $reglas['formato'] = ['label' => 'formato de salida', 'type' => 'enum', 'allowed' => ['csv', 'excel', 'pdf'], 'required' => true];
        }

        $datos = Validator::validar($_GET, $reglas);

        if (isset($datos['desde']) && isset($datos['hasta']) && $datos['desde'] !== '' && $datos['hasta'] !== '') {
            if ($datos['hasta'] < $datos['desde']) {
                throw new ValidationException(['hasta' => 'La fecha inicial no puede ser posterior a la final']);
            }
        }

        return $datos;
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
