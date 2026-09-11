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
    public static function csv(string $titulo, array $columnas, array $filas): void
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
     * @param string $titulo
     * @param array  $columnas
     * @param array  $filas
     */
    public static function excel(string $titulo, array $columnas, array $filas): void
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Datos</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        $html .= '<body><table border="1"><thead><tr>';
        foreach ($columnas as $col) {
            $html .= '<th>' . htmlspecialchars((string)$col, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            $html .= '<tr>';
            foreach (self::filaPlana($fila) as $valor) {
                $html .= '<td>' . htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        self::send("application/vnd.ms-excel; charset=UTF-8", self::nombre($titulo, 'xls'), $html);
    }

    /**
     * Genera un archivo PDF descargable (PDF mínimo y válido, texto + tabla).
     *
     * @param string $titulo
     * @param array  $columnas
     * @param array  $filas
     * @param int    $anchoMax  PyX ancho aproximado de página.
     */
    public static function pdf(string $titulo, array $columnas, array $filas): void
    {
        $pdf = new PdfBuilder();
        $pdf->addText($titulo, 14, true);
        $pdf->addText('Generado: ' . date('d/m/Y H:i'), 10);
        $pdf->addText('');
        $pdf->addTable($columnas, $filas);
        self::send("application/pdf", self::nombre($titulo, 'pdf'), $pdf->output());
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
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $contenido;
    }
}
