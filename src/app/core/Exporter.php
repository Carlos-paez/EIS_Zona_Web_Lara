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
     */
    public static function csv(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $columnas);
        foreach ($filas as $fila) {
            fputcsv($out, self::filaPlana($fila), ',', '"', '\\');
        }
        rewind($out);
        $contenido = stream_get_contents($out);
        fclose($out);
        if (substr($contenido, -1) !== "\n" && substr($contenido, -1) !== "\r") {
            $contenido .= "\r\n";
        }
        $contenido = preg_replace("/([^\r])\n/", "$1\r\n", $contenido);
        $contenido = preg_replace("/\r\n?/", "\r\n", $contenido);

        self::send("text/csv; charset=UTF-8", self::nombre($titulo, 'csv', $meta), "\xEF\xBB\xBF" . $contenido);
    }

    /**
     * Genera un archivo Excel (formato HTML que Excel abre correctamente).
     *
     * @param string $titulo
     * @param array  $columnas
     * @param array  $filas
     */
    public static function excel(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Datos</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body><table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;">';
        $html .= '<thead><tr style="background:#4472C4;color:#ffffff;font-weight:bold;">';
        foreach ($columnas as $col) {
            $html .= '<th style="border:1px solid #000000;">' . htmlspecialchars((string)$col, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        $i = 0;
        foreach ($filas as $fila) {
            $bg = ($i % 2 === 0) ? 'background:#F2F7FF;' : 'background:#FFFFFF;';
            $html .= '<tr style="' . $bg . '">';
            foreach (self::filaPlana($fila) as $valor) {
                $html .= '<td style="border:1px solid #CCCCCC;mso-number-format:\@;">' . htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
            $i++;
        }
        $html .= '</tbody></table></body></html>';

        self::send("application/vnd.ms-excel; charset=UTF-8", self::nombre($titulo, 'xls', $meta), $html);
    }

    /**
     * Genera un archivo PDF descargable (PDF mínimo y válido, texto + tabla).
     *
     * @param string $titulo
     * @param array  $columnas
     * @param array  $filas
     * @param int    $anchoMax  PyX ancho aproximado de página.
     */
    public static function pdf(string $titulo, array $columnas, array $filas, array $meta = []): void
    {
        $pdf = new PdfBuilder();
        $pdf->addText($titulo, 14, true);
        $pdf->addText('Generado: ' . date('d/m/Y H:i'), 10);
        if (!empty($meta['desde']) || !empty($meta['hasta'])) {
            $desde = $meta['desde'] ?? '';
            $hasta = $meta['hasta'] ?? '';
            $pdf->addText('Periodo: ' . $desde . ' - ' . $hasta, 9);
        }
        $pdf->addText('');
        $pdf->addTable($columnas, $filas);
        self::send("application/pdf", self::nombre($titulo, 'pdf', $meta), $pdf->output());
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

    private static function nombre(string $titulo, string $ext, array $meta = []): string
    {
        $base = trim((string)$titulo);
        $base = mb_convert_encoding($base, 'ASCII', 'UTF-8');
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '_', strtolower($base));
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'reporte';
        }
        if (!empty($meta['tipo'])) {
            $tipo = mb_convert_encoding((string)$meta['tipo'], 'ASCII', 'UTF-8');
            $tipo = preg_replace('/[^A-Za-z0-9_\-]+/', '_', strtolower($tipo));
            $tipo = trim($tipo, '_');
            if ($tipo !== '') {
                $base = $tipo . '_' . $base;
            }
        }
        if (!empty($meta['desde']) && !empty($meta['hasta'])) {
            $desde = preg_replace('/[^0-9]/', '', (string)$meta['desde']);
            $hasta = preg_replace('/[^0-9]/', '', (string)$meta['hasta']);
            if ($desde !== '' && $hasta !== '') {
                $base .= '_' . $desde . '_' . $hasta;
            }
        }
        return $base . '_' . date('Ymd_His') . '.' . $ext;
    }

    private static function send(string $mime, string $filename, string $contenido): void
    {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $contenido;
    }
}
