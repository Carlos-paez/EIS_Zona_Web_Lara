<?php

namespace App\Controllers;

use App\Core\Exporter;
use App\Core\Logger;
use App\Core\Router;
use App\Core\Validator;
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
            Logger::error($e, 'Reportes');
            $this->json(false, null, 'Error interno del servidor');
        }
    }

    private function kpis(): void
    {
        $this->json(true, $this->model->kpis());
    }

    private function consultar(): void
    {
        $tipo   = $this->paramTexto('tipo');
        $desde  = $this->paramTexto('desde');
        $hasta  = $this->paramTexto('hasta');
        $this->validarRango($tipo, $desde, $hasta);

        $resultado = $this->model->consultar($tipo, $desde, $hasta, $this->filtrosDesdeRequest());
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $resultado]);
    }

    private function exportar(): void
    {
        if (!Router::verifyCsrfToken($this->paramTexto('csrf_token'))) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $tipo    = $this->paramTexto('tipo');
        $formato = strtolower($this->paramTexto('formato'));
        $desde   = $this->paramTexto('desde');
        $hasta   = $this->paramTexto('hasta');
        $this->validarRango($tipo, $desde, $hasta);

        if (!in_array($formato, ['csv', 'excel', 'pdf'], true)) {
            throw new \InvalidArgumentException('Formato de salida no válido');
        }

        $resultado = $this->model->consultar($tipo, $desde, $hasta, $this->filtrosDesdeRequest());
        $titulo    = $this->model->nombreTipo($tipo);

        if (!empty($resultado['agrupacion'])) {
            $titulo .= ' · ' . $resultado['agrupacion'];
        }

        $meta = [
            'desde'      => Reporte::usaFechas($tipo) ? $desde : '',
            'hasta'      => Reporte::usaFechas($tipo) ? $hasta : '',
            'registros'  => count($resultado['filas']),
            'agrupacion' => $resultado['agrupacion'] ?? '',
            'filtros'    => $resultado['contexto'] ?? [],
            'resumen'    => $resultado['resumen'] ?? [],
        ];

        match ($formato) {
            'csv'   => Exporter::csv($titulo, $resultado['columnas'], $resultado['filas'], $meta),
            'excel' => Exporter::excel($titulo, $resultado['columnas'], $resultado['filas'], $meta),
            'pdf'   => Exporter::pdf($titulo, $resultado['columnas'], $resultado['filas'], $meta),
        };
    }

    /**
     * Reúne los filtros contextuales enviados por el formulario.
     *
     * El modelo valida cada valor contra listas blancas y convierte los tipos,
     * de modo que los parámetros no válidos se ignoran de forma segura.
     */
    private function filtrosDesdeRequest(): array
    {
        return [
            'agrupar'      => $_GET['agrupar'] ?? '',
            'orden'        => $_GET['orden'] ?? '',
            'limite'       => $_GET['limite'] ?? 0,
            'cliente'      => $_GET['cliente'] ?? 0,
            'usuario'      => $_GET['usuario'] ?? 0,
            'categoria'    => $_GET['categoria'] ?? 0,
            'producto'     => $_GET['producto'] ?? 0,
            'proveedor'    => $_GET['proveedor'] ?? 0,
            'estado'       => $_GET['estado'] ?? 0,
            'activo'       => $_GET['activo'] ?? 0,
            'monto_min'    => $_GET['monto_min'] ?? 0,
            'estado_stock' => $_GET['estado_stock'] ?? 'todos',
            'estado_ciber' => $_GET['estado_ciber'] ?? '',
        ];
    }

    /**
     * Lee un parámetro GET escalar y descarta arrays u objetos manipulados.
     */
    private function paramTexto(string $nombre, string $default = ''): string
    {
        $valor = $_GET[$nombre] ?? $default;
        return is_scalar($valor) ? trim((string)$valor) : $default;
    }

    private function validarRango(string $tipo, string $desde, string $hasta): void
    {
        if (!Reporte::existeTipo($tipo)) {
            throw new \InvalidArgumentException('Tipo de reporte no válido');
        }

        if (!Reporte::usaFechas($tipo)) {
            return;
        }

        $desde = Validator::fecha($desde, 'fecha inicial', ['required' => true]);
        $hasta = Validator::fecha($hasta, 'fecha final', ['required' => true]);
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
