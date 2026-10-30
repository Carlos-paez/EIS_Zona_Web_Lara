<?php

namespace App\Core;

/**
 * Exportador de datos en distintos formatos sin dependencias externas.
 *
 * Convierte un arreglo [columnas => [...], filas => [...]] en contenido y
 * encabezados HTTP para descargar como CSV, Excel (HTML) o PDF.
 */
class Exporter
{
    /**
     * Genera un archivo CSV descargable.
     *
     * @param string $titulo   Nombre base del archivo.
     * @param array  $columnas
     * @param array  $filas
     * @param array  $meta     Contexto del reporte (no se incluye en el CSV).
     */
    public static function csv(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $columnas);
        foreach ($filas as $fila) {
            fputcsv($out, self::filaPlana($fila));
        }
        rewind($out);
        $contenido = stream_get_contents($out);
        fclose($out);

        self::send("text/csv; charset=UTF-8", self::nombre($titulo, 'csv'), "\xEF\xBB\xBF" . $contenido);
    }

    /**
     * Genera un archivo Excel (formato HTML que Excel abre correctamente).
     *
     * Incluye una cabecera corporativa, encabezados con color, filas alternas,
     * formatos numéricos y anchos de columna calculados según el contenido.
     */
    public static function excel(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $columnas = array_values($columnas);
        $nCols = max(1, count($columnas));
        $anchos = self::anchosExcel($columnas, $filas);

        $colgroup = '<colgroup>';
        foreach ($anchos as $ancho) {
            $colgroup .= '<col style="width:' . $ancho . 'px;">';
        }
        $colgroup .= '</colgroup>';

        $metaHtml = self::metaHtml($meta, count($filas));
        $fuente = "font-family:'Segoe UI',Calibri,Arial,sans-serif;";

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" '
            . 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
            . 'xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $html .= '<title>' . self::e($titulo) . '</title>';
        $html .= '<style>'
            . 'table.reporte{border-collapse:collapse;table-layout:fixed;}'
            . 'table.reporte td,table.reporte th{white-space:normal;word-wrap:break-word;}'
            . '</style>';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>'
            . '<x:Name>' . self::e(mb_substr($titulo, 0, 31)) . '</x:Name>'
            . '<x:WorksheetOptions><x:DisplayGridlines/><x:Print>'
            . '<x:ValidPrinterInfo/><x:Orientation>Landscape</x:Orientation>'
            . '<x:FitWidth>1</x:FitWidth><x:Scale>100</x:Scale>'
            . '</x:Print></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets>'
            . '</x:ExcelWorkbook></xml><![endif]-->';
        $html .= '</head><body>';

        $html .= '<table class="reporte">' . $colgroup;

        // Cabecera corporativa
        $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
            . 'background:#283593;color:#c5cae9;font-size:9pt;font-weight:bold;'
            . 'letter-spacing:1.5pt;padding:10pt 10pt 0;">EIS SYSTEM</td></tr>';

        $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
            . 'background:#283593;color:#ffffff;font-size:17pt;font-weight:bold;'
            . 'padding:2pt 10pt 3pt;">' . self::e($titulo) . '</td></tr>';

        $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
            . 'background:#283593;color:#9fa8da;font-size:9pt;'
            . 'padding:0 10pt 9pt;">Sistema de Gestión · Zona Web Lara</td></tr>';

        if ($metaHtml !== '') {
            $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
                . 'background:#f4f6fe;color:#5c6180;font-size:9pt;'
                . 'border-bottom:1pt solid #d8dcea;padding:7pt 10pt;">' . $metaHtml . '</td></tr>';
        }

        $html .= '<tr><td colspan="' . $nCols . '" style="height:7pt;"></td></tr>';

        // Encabezado de la tabla
        $html .= '<tr>';
        foreach ($columnas as $col) {
            $html .= '<th style="' . $fuente
                . 'background:#3949ab;color:#ffffff;font-size:9.5pt;font-weight:bold;'
                . 'text-align:center;vertical-align:middle;'
                . 'border:0.75pt solid #2f3d8f;padding:7pt 5pt;">'
                . self::e((string)$col) . '</th>';
        }
        $html .= '</tr>';

        $tipos = [];
        foreach ($columnas as $i => $col) {
            $tipos[] = self::tipoColumna((string)$col, $filas, $i);
        }

        if (!$filas) {
            $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
                . 'background:#f4f6fe;color:#676f85;font-size:9.5pt;font-style:italic;'
                . 'text-align:center;border:0.5pt solid #d8dcea;padding:14pt;">'
                . 'No hay registros para los criterios seleccionados.</td></tr>';
        } else {
            foreach ($filas as $idx => $fila) {
                $valores = self::filaPlana($fila);
                $fondo = $idx % 2 === 1 ? '#f4f6fe' : '#ffffff';

                $html .= '<tr>';
                foreach ($columnas as $i => $col) {
                    $valor = trim((string)($valores[$i] ?? ''));
                    $tipo = $tipos[$i] ?? 'text';
                    $formato = self::formatoExcel($tipo);

                    $html .= '<td style="' . $fuente
                        . 'background:' . $fondo . ';color:#282d3d;font-size:9pt;'
                        . 'vertical-align:middle;border:0.5pt solid #d8dcea;'
                        . 'padding:6pt 5pt;' . $formato . '">'
                        . self::e($valor) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        // Pie del documento
        $html .= '<tr><td colspan="' . $nCols . '" style="' . $fuente
            . 'color:#8a90a6;font-size:7.5pt;text-align:right;padding:8pt 4pt 0;">'
            . 'Documento generado automáticamente por EIS System · Zona Web Lara</td></tr>';

        $html .= '</table></body></html>';

        self::send("application/vnd.ms-excel; charset=UTF-8", self::nombre($titulo, 'xls'), "\xEF\xBB\xBF" . $html);
    }

    /**
     * Genera un archivo PDF descargable con diseño corporativo.
     *
     * @param string $titulo
     * @param array  $columnas
     * @param array  $filas
     * @param array  $meta  Contexto: desde, hasta y registros.
     */
    public static function pdf(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $pdf = new PdfBuilder();
        $pdf->setDocumento(
            $titulo,
            'Sistema de Gestión · Zona Web Lara',
            self::describirMeta($meta, count($filas))
        );
        $pdf->addTable($columnas, $filas);

        self::send("application/pdf", self::nombre($titulo, 'pdf'), $pdf->output());
    }

    // ------------------------------------------------------------------
    // Utilidades comunes
    // ------------------------------------------------------------------

    /**
     * Datos de contexto comunes para PDF y Excel.
     *
     * @return string[]
     */
    private static function describirMeta(array $meta, int $registros): array
    {
        $items = [];

        $desde = trim((string)($meta['desde'] ?? ''));
        $hasta = trim((string)($meta['hasta'] ?? ''));

        if ($desde !== '' && $hasta !== '') {
            $items[] = 'Periodo: ' . self::fecha($desde) . ' al ' . self::fecha($hasta);
        } elseif ($desde !== '') {
            $items[] = 'Desde: ' . self::fecha($desde);
        } elseif ($hasta !== '') {
            $items[] = 'Hasta: ' . self::fecha($hasta);
        }

        $agrupacion = trim((string)($meta['agrupacion'] ?? ''));
        if ($agrupacion !== '') {
            $items[] = 'Agrupación: ' . $agrupacion;
        }

        foreach ((array)($meta['filtros'] ?? []) as $filtro) {
            $filtro = trim((string)$filtro);
            if ($filtro !== '') {
                $items[] = $filtro;
            }
        }

        $partesResumen = [];
        foreach ((array)($meta['resumen'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string)($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $partesResumen[] = $label . ': ' . self::valorResumen($item);
        }
        if ($partesResumen) {
            $items[] = 'Resumen: ' . implode(' · ', $partesResumen);
        }

        $items[] = 'Generado: ' . date('d/m/Y H:i');
        $items[] = $registros . ($registros === 1 ? ' registro' : ' registros');

        // El contexto y el campo de agrupación pueden coincidir; evito
        // repetir la misma etiqueta en el encabezado del archivo.
        return array_values(array_unique($items));
    }

    /**
     * Formatea un valor del resumen ejecutivo para PDF/Excel.
     */
    private static function valorResumen(array $item): string
    {
        $valor   = $item['valor'] ?? '';
        $formato = (string)($item['formato'] ?? 'text');

        if ($formato === 'text' || !is_numeric($valor)) {
            return trim((string)$valor);
        }

        $numero = (float)$valor;

        return match ($formato) {
            'money'   => '$' . number_format($numero, 2, ',', '.'),
            'decimal' => number_format($numero, 2, ',', '.'),
            'int'     => number_format($numero, 0, ',', '.'),
            default   => trim((string)$valor),
        };
    }

    private static function metaHtml(array $meta, int $registros): string
    {
        $items = self::describirMeta($meta, $registros);
        return implode(' &nbsp;|&nbsp; ', array_map([self::class, 'e'], $items));
    }

    private static function fecha(string $fecha): string
    {
        $dt = \DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10));
        return $dt ? $dt->format('d/m/Y') : $fecha;
    }

    private static function tipoColumna(string $columna, array $filas, int $indice): string
    {
        if (self::esIdentificador($columna)) {
            return 'text';
        }

        $tieneValores = false;
        $decimales = false;

        foreach ($filas as $fila) {
            $valores = array_values(is_array($fila) ? $fila : []);
            $valor = trim((string)($valores[$indice] ?? ''));
            if ($valor === '') {
                continue;
            }
            if (!is_numeric($valor)) {
                return 'text';
            }
            $tieneValores = true;
            if (abs((float)$valor - round((float)$valor)) > 0.00001) {
                $decimales = true;
            }
        }

        if (!$tieneValores) {
            return 'text';
        }

        if (self::esMoneda($columna)) {
            return 'money';
        }

        return $decimales ? 'decimal' : 'int';
    }

    private static function formatoExcel(string $tipo): string
    {
        return match ($tipo) {
            'money'   => "text-align:right;mso-number-format:'\\\$#,##0.00';",
            'decimal' => "text-align:right;mso-number-format:'#,##0.00';",
            'int'     => "text-align:right;mso-number-format:'#,##0';",
            default   => "text-align:left;mso-number-format:'\\@';",
        };
    }

    /**
     * Ancho aproximado (px) de cada columna de Excel.
     *
     * @return int[]
     */
    private static function anchosExcel(array $columnas, array $filas): array
    {
        $anchos = [];

        foreach ($columnas as $i => $col) {
            $max = mb_strlen((string)$col);

            foreach (array_slice($filas, 0, 300) as $fila) {
                $valores = array_values(is_array($fila) ? $fila : []);
                $valor = $valores[$i] ?? '';
                if (is_array($valor)) {
                    $valor = json_encode($valor, JSON_UNESCAPED_UNICODE);
                }
                $max = max($max, mb_strlen((string)$valor));
            }

            $anchos[] = (int)max(80, min(280, 32 + $max * 7));
        }

        return $anchos;
    }

    private static function esMoneda(string $columna): bool
    {
        return (bool)preg_match('/total|costo|precio|venta|valor|monto|importe|subtotal|ingreso/i', $columna);
    }

    private static function esIdentificador(string $columna): bool
    {
        return (bool)preg_match('/^(id|c[oó]digo|n[°º]?\s*orden|orden|a[nñ]o)$/iu', trim($columna));
    }

    private static function filaPlana(array $fila): array
    {
        $plana = [];
        foreach ($fila as $valor) {
            if (is_array($valor)) {
                $plana[] = json_encode($valor, JSON_UNESCAPED_UNICODE);
            } else {
                $plana[] = (string)$valor;
            }
        }
        return $plana;
    }

    private static function e(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private static function nombre(string $titulo, string $ext): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', strtolower(trim($titulo)));
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'reporte';
        }
        return $base . '_' . date('Ymd_His') . '.' . $ext;
    }

    private static function send(string $mime, string $filename, string $contenido): void
    {
        if (!headers_sent()) {
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($contenido));
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        echo $contenido;
    }
}
