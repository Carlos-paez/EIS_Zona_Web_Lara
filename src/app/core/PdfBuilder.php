<?php

namespace App\Core;

/**
 * Generador de PDF válido en PHP puro (sin librerías externas).
 *
 * Produce documentos con una portada/encabezado corporativo, tabla con
 * filas alternas, columnas ajustadas al contenido, numeración de páginas
 * y pie de página. Soporta caracteres Latin-1.
 *
 * Coordenadas lógicas: origen arriba-izquierda, medidas en milímetros.
 * Cada página aplica una transformación para que el PDF resultante sea
 * un A4 real (210 x 297 mm) al imprimirlo.
 */
class PdfBuilder
{
    private float $pageWidth;
    private float $pageHeight;
    private float $marginLeft;
    private float $marginRight;
    private float $cursorY;

    /** @var string[] Contenido (stream) por página. */
    private array $pages = [];

    private bool $pageStarted = false;

    private string $titulo = '';
    private string $subtitulo = '';
    private array $meta = [];

    /** @var array<int, float> Anchos de columna de la tabla activa. */
    private array $columnWidths = [];

    /** @var array<int, string> Alineación de cada columna (left|right|center). */
    private array $columnAligns = [];

    /** @var array<int, string> Encabezados de la tabla activa. */
    private array $tableColumns = [];

    /** Factor de conversión milímetros -> puntos PDF. */
    private const PT_PER_MM = 2.834645669;

    /** 1 punto = 0.352778 mm aprox. */
    private const UNITS = 0.352778;

    // Paleta corporativa (coincide con la interfaz del sistema).
    private const COLOR_PRIMARY      = [0.224, 0.286, 0.671]; // #3949ab
    private const COLOR_PRIMARY_DARK = [0.157, 0.208, 0.576]; // #283593
    private const COLOR_ACCENT       = [0.118, 0.533, 0.898]; // #1e88e5
    private const COLOR_TEXT         = [0.157, 0.176, 0.239]; // #282d3d
    private const COLOR_MUTED        = [0.404, 0.435, 0.522]; // #676f85
    private const COLOR_BORDER       = [0.847, 0.863, 0.918]; // #d8dcea
    private const COLOR_ZEBRA        = [0.957, 0.965, 0.996]; // #f4f6fe
    private const COLOR_WHITE        = [1.0, 1.0, 1.0];

    public function __construct()
    {
        $this->pageWidth   = 210;
        $this->pageHeight  = 297;
        $this->marginLeft  = 14;
        $this->marginRight = 14;
        $this->cursorY     = 0;
    }

    /**
     * Define el encabezado del documento.
     *
     * @param string   $titulo    Título principal del reporte.
     * @param string   $subtitulo Línea descriptiva bajo el título.
     * @param string[] $meta      Datos de contexto (periodo, fecha, registros).
     */
    public function setDocumento(string $titulo, string $subtitulo = '', array $meta = []): void
    {
        $this->titulo    = trim($titulo);
        $this->subtitulo = trim($subtitulo);
        $this->meta      = array_values(array_filter(array_map('trim', $meta), static fn ($v) => $v !== ''));
    }

    /**
     * Añade una línea de texto libre.
     */
    public function addText(string $texto, float $size = 11, bool $bold = false): void
    {
        $this->ensurePage();

        $lineHeight = $size * self::UNITS * 1.45;
        if ($this->cursorY + $lineHeight > $this->contentBottom()) {
            $this->startPage();
        }

        $baseline = $this->cursorY + $size * self::UNITS * 0.82;
        $this->text($this->marginLeft, $baseline, $texto, $size, $bold, self::COLOR_TEXT);
        $this->cursorY += $lineHeight + 1;
    }

    /**
     * Añade una tabla con encabezado fijo, filas alternas y ajuste de texto.
     */
    public function addTable(array $columnas, array $filas): void
    {
        $this->ensurePage();

        $this->tableColumns  = array_values($columnas);
        $this->columnWidths  = $this->calcularAnchos($this->tableColumns, $filas);
        $this->columnAligns  = $this->detectarAlineaciones($this->tableColumns, $filas);

        $this->drawTableHeader();

        if (!$filas) {
            $this->drawEmptyRow();
            return;
        }

        $zebra = false;
        foreach ($filas as $fila) {
            $this->drawTableRow(self::filaPlana($fila), $zebra);
            $zebra = !$zebra;
        }
    }

    /**
     * Devuelve el contenido binario del PDF.
     */
    public function output(): string
    {
        if (!$this->pages) {
            $this->startPage();
        }

        $total = count($this->pages);
        foreach ($this->pages as $i => $contenido) {
            $contenido = str_replace(
                ['{{PAGINA}}', '{{TOTAL}}'],
                [(string)($i + 1), (string)$total],
                $contenido
            );
            $this->pages[$i] = $contenido . "Q\n";
        }

        $n = count($this->pages);

        // Estructura de objetos:
        //   1 = Catalog, 2 = Pages
        //   páginas: 2*i+1, streams: 2*i+2  (i = 1..n)
        //   fuentes: después de todos los streams
        $fontReg = 2 + 2 * $n + 1;
        $fontBold = $fontReg + 1;

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];
        for ($i = 1; $i <= $n; $i++) {
            $kids[] = (2 + 2 * $i - 1) . ' 0 R';
        }
        $objects[2] = sprintf('<< /Type /Pages /Kids [%s] /Count %d >>', implode(' ', $kids), $n);

        $mediaWidth  = $this->num($this->pageWidth * self::PT_PER_MM);
        $mediaHeight = $this->num($this->pageHeight * self::PT_PER_MM);

        for ($i = 1; $i <= $n; $i++) {
            $pageId = 2 + 2 * $i - 1;
            $contId = 2 + 2 * $i;
            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %s %s] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>',
                $mediaWidth,
                $mediaHeight,
                $fontReg,
                $fontBold,
                $contId
            );
            $objects[$contId] = "<< /Length " . strlen($this->pages[$i - 1]) . " >>\nstream\n" . $this->pages[$i - 1] . "endstream";
        }

        $objects[$fontReg] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[$fontBold] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }

    // ------------------------------------------------------------------
    // Páginas y encabezados
    // ------------------------------------------------------------------

    private function ensurePage(): void
    {
        if (!$this->pageStarted) {
            $this->startPage();
        }
    }

    private function startPage(): void
    {
        $this->pages[] = "q\n" . $this->num(self::PT_PER_MM) . " 0 0 " . $this->num(self::PT_PER_MM) . " 0 0 cm\n";
        $this->pageStarted = true;

        if (count($this->pages) === 1) {
            $this->drawCoverHeader();
        } else {
            $this->drawCompactHeader();
        }

        $this->drawFooter();
    }

    private function drawCoverHeader(): void
    {
        $this->fillRect(0, 0, $this->pageWidth, 6.5, self::COLOR_PRIMARY_DARK);
        $this->fillRect(0, 6.5, $this->pageWidth, 1.2, self::COLOR_ACCENT);

        $this->text($this->marginLeft, 16.5, 'EIS SYSTEM', 8.5, true, self::COLOR_PRIMARY);

        $titulo = $this->titulo !== '' ? $this->titulo : 'Reporte';
        $tituloSize = 19;
        while ($tituloSize > 12 && $this->anchoTexto($titulo, $tituloSize, true) > $this->maxWidth()) {
            $tituloSize--;
        }
        $this->text($this->marginLeft, 25.5, $titulo, $tituloSize, true, self::COLOR_PRIMARY_DARK);

        $y = 33.5;
        if ($this->subtitulo !== '') {
            $this->text($this->marginLeft, $y, $this->subtitulo, 9.5, false, self::COLOR_MUTED);
            $y += 7;
        }

        if ($this->meta) {
            $textoMeta = implode('   |   ', $this->meta);
            foreach ($this->envolver($textoMeta, $this->maxWidth(), 8.3) as $lineaMeta) {
                $this->text($this->marginLeft, $y, $lineaMeta, 8.3, false, self::COLOR_MUTED);
                $y += 3.7;
            }
            $y += 2.3;
        }

        $ruleY = $y + 1;
        $this->line($this->marginLeft, $ruleY, $this->pageWidth - $this->marginRight, $ruleY, self::COLOR_BORDER, 0.3);
        $this->line($this->marginLeft, $ruleY, $this->marginLeft + 34, $ruleY, self::COLOR_PRIMARY, 1.1);

        $this->cursorY = $ruleY + 8;
    }

    private function drawCompactHeader(): void
    {
        $this->fillRect(0, 0, $this->pageWidth, 4.5, self::COLOR_PRIMARY_DARK);

        $this->text($this->marginLeft, 12.5, $this->titulo !== '' ? $this->titulo : 'Reporte', 10.5, true, self::COLOR_PRIMARY_DARK);
        $this->text($this->marginLeft, 17.5, 'EIS System · Zona Web Lara', 7.5, false, self::COLOR_MUTED);

        $this->line($this->marginLeft, 21, $this->pageWidth - $this->marginRight, 21, self::COLOR_BORDER, 0.3);
        $this->cursorY = 28;
    }

    private function drawFooter(): void
    {
        $y = $this->pageHeight - 12;

        $this->line($this->marginLeft, $y - 3.5, $this->pageWidth - $this->marginRight, $y - 3.5, self::COLOR_BORDER, 0.25);

        $this->text($this->marginLeft, $y + 0.5, 'EIS System · Zona Web Lara', 7, false, self::COLOR_MUTED);

        // Se mide con un texto de referencia para que el borde derecho quede alineado.
        $textoPagina = 'Página {{PAGINA}} de {{TOTAL}}';
        $referencia  = 'Página 00 de 00';
        $x = $this->pageWidth - $this->marginRight - $this->anchoTexto($referencia, 7);
        $this->text($x, $y + 0.5, $textoPagina, 7, false, self::COLOR_MUTED);
    }

    private function contentBottom(): float
    {
        return $this->pageHeight - 21;
    }

    // ------------------------------------------------------------------
    // Tabla
    // ------------------------------------------------------------------

    private function drawTableHeader(): void
    {
        $size   = 8.5;
        $padX   = 2.6;
        $padY   = 2.5;
        $lineH  = $size * self::UNITS * 1.35;

        $lineas = [];
        $maxLineas = 1;
        foreach ($this->tableColumns as $i => $col) {
            $lineas[$i] = $this->envolver((string)$col, $this->columnWidths[$i] - $padX * 2, $size, true);
            $maxLineas = max($maxLineas, count($lineas[$i]));
        }

        $alto = $maxLineas * $lineH + $padY * 2;

        if ($this->cursorY + $alto > $this->contentBottom()) {
            $this->startPage();
        }

        $top = $this->cursorY;
        $this->fillRect($this->marginLeft, $top, $this->maxWidth(), $alto, self::COLOR_PRIMARY_DARK);

        $x = $this->marginLeft;
        foreach ($this->tableColumns as $i => $col) {
            if ($i > 0) {
                $this->line($x, $top, $x, $top + $alto, [0.36, 0.42, 0.76], 0.25);
            }
            $baseline = $top + $padY + $size * self::UNITS * 0.82;
            $align = $this->columnAligns[$i] ?? 'left';
            foreach ($lineas[$i] as $linea) {
                $tx = $x + $padX;
                if ($align === 'right') {
                    $tx = $x + $this->columnWidths[$i] - $padX - $this->anchoTexto($linea, $size, true);
                }
                $this->text($tx, $baseline, $linea, $size, true, self::COLOR_WHITE);
                $baseline += $lineH;
            }
            $x += $this->columnWidths[$i];
        }

        $this->cursorY = $top + $alto;
    }

    private function drawTableRow(array $valores, bool $zebra): void
    {
        $size  = 8.2;
        $padX  = 2.6;
        $padY  = 2.2;
        $lineH = $size * self::UNITS * 1.35;

        $lineas = [];
        $maxLineas = 1;
        foreach ($this->tableColumns as $i => $col) {
            $texto = $this->formatearValor($valores[$i] ?? '', $col);
            $lineas[$i] = $this->envolver($texto, $this->columnWidths[$i] - $padX * 2, $size, false);
            $maxLineas = max($maxLineas, count($lineas[$i]));
        }

        $alto = max(7.6, $maxLineas * $lineH + $padY * 2);

        if ($this->cursorY + $alto > $this->contentBottom()) {
            $this->startPage();
            $this->drawTableHeader();
        }

        $top = $this->cursorY;

        if ($zebra) {
            $this->fillRect($this->marginLeft, $top, $this->maxWidth(), $alto, self::COLOR_ZEBRA);
        }

        $this->line($this->marginLeft, $top + $alto, $this->pageWidth - $this->marginRight, $top + $alto, self::COLOR_BORDER, 0.25);

        $x = $this->marginLeft;
        foreach ($this->tableColumns as $i => $col) {
            if ($i > 0) {
                $this->line($x, $top, $x, $top + $alto, [0.91, 0.92, 0.96], 0.2);
            }

            $baseline = $top + $padY + $size * self::UNITS * 0.82;
            foreach ($lineas[$i] as $linea) {
                $tx = $x + $padX;
                $align = $this->columnAligns[$i] ?? 'left';
                if ($align === 'right') {
                    $tx = $x + $this->columnWidths[$i] - $padX - $this->anchoTexto($linea, $size);
                }
                $this->text($tx, $baseline, $linea, $size, false, self::COLOR_TEXT);
                $baseline += $lineH;
            }
            $x += $this->columnWidths[$i];
        }

        $this->cursorY = $top + $alto;
    }

    private function drawEmptyRow(): void
    {
        $alto = 14;

        if ($this->cursorY + $alto > $this->contentBottom()) {
            $this->startPage();
            $this->drawTableHeader();
        }

        $top = $this->cursorY;
        $this->fillRect($this->marginLeft, $top, $this->maxWidth(), $alto, self::COLOR_ZEBRA);
        $this->line($this->marginLeft, $top + $alto, $this->pageWidth - $this->marginRight, $top + $alto, self::COLOR_BORDER, 0.25);

        $texto = 'No hay registros para los criterios seleccionados.';
        $x = $this->marginLeft + ($this->maxWidth() - $this->anchoTexto($texto, 8.5)) / 2;
        $this->text($x, $top + $alto / 2 + 1.2, $texto, 8.5, false, self::COLOR_MUTED);

        $this->cursorY = $top + $alto;
    }

    /**
     * Reparte el ancho disponible según el contenido de cada columna.
     *
     * @return array<int, float>
     */
    private function calcularAnchos(array $columnas, array $filas): array
    {
        $total = $this->maxWidth();
        $pad = 2.6;

        $necesidades = [];
        $minimos = [];
        $rigidas = [];

        foreach ($columnas as $i => $col) {
            $col = (string)$col;
            $headerW = $this->anchoTexto($col, 8.5, true);
            $maxW = 0;
            $suma = 0;
            $n = 0;

            foreach ($filas as $fila) {
                $valores = array_values(is_array($fila) ? $fila : []);
                $texto = $this->formatearValor($valores[$i] ?? '', $col);
                $w = $this->anchoTexto($texto, 8.2);
                $maxW = max($maxW, $w);
                $suma += min($w, 55);
                $n++;
            }

            $promedio = $n > 0 ? $suma / $n : 0;
            $identificador = $this->esIdentificador($col);
            $numerica = !$identificador && $this->columnaNumerica($col, $filas, $i);
            $rigida = $identificador || $numerica;

            $contenido = $rigida
                ? max($headerW, $maxW)
                : max($headerW + 2, min($maxW, 42), $promedio * 1.1);

            $necesidades[] = max(13, $contenido + $pad * 2 + 0.6);
            $minimos[] = max(
                $headerW + $pad * 2 + 0.6,
                $rigida ? $maxW + $pad * 2 + 0.6 : 15
            );
            $rigidas[] = $rigida;
        }

        $sumaNecesidades = array_sum($necesidades) ?: 1;

        if ($sumaNecesidades <= $total) {
            $extra = $total - $sumaNecesidades;
            $widths = [];
            foreach ($necesidades as $necesidad) {
                $widths[] = $necesidad + $extra * ($necesidad / $sumaNecesidades);
            }
            return $widths;
        }

        // No cabe: se reduce primero el ancho de las columnas que pueden
        // partir su contenido en varias líneas (texto libre).
        $widths = $necesidades;
        $exceso = $sumaNecesidades - $total;

        $reducible = [];
        $totalReducible = 0;
        foreach ($widths as $i => $w) {
            if (!$rigidas[$i]) {
                $reducible[$i] = max(0, $w - $minimos[$i]);
                $totalReducible += $reducible[$i];
            }
        }

        if ($totalReducible > 0) {
            $usar = min($exceso, $totalReducible);
            foreach ($reducible as $i => $r) {
                $widths[$i] -= $usar * ($r / $totalReducible);
            }
            $exceso -= $usar;
        }

        if ($exceso > 0.01) {
            $reducible = [];
            $totalReducible = 0;
            foreach ($widths as $i => $w) {
                $piso = max(9, $minimos[$i] * 0.72);
                $reducible[$i] = max(0, $w - $piso);
                $totalReducible += $reducible[$i];
            }

            if ($totalReducible > 0) {
                $usar = min($exceso, $totalReducible);
                foreach ($reducible as $i => $r) {
                    $widths[$i] -= $usar * ($r / $totalReducible);
                }
            }
        }

        if ($widths) {
            $ultimo = count($widths) - 1;
            $widths[$ultimo] += $total - array_sum($widths);
        }

        return $widths;
    }

    private function columnaNumerica(string $columna, array $filas, int $indice): bool
    {
        $tieneValores = false;

        foreach ($filas as $fila) {
            $valores = array_values(is_array($fila) ? $fila : []);
            $valor = trim((string)($valores[$indice] ?? ''));
            if ($valor === '') {
                continue;
            }
            if (!is_numeric($valor)) {
                return false;
            }
            $tieneValores = true;
        }

        return $tieneValores;
    }

    /**
     * Determina qué columnas se alinean a la derecha (valores numéricos).
     *
     * @return array<int, string>
     */
    private function detectarAlineaciones(array $columnas, array $filas): array
    {
        $aligns = [];

        foreach ($columnas as $i => $col) {
            $numerica = false;
            $tieneValores = false;

            foreach ($filas as $fila) {
                $valores = array_values(is_array($fila) ? $fila : []);
                $valor = trim((string)($valores[$i] ?? ''));
                if ($valor === '') {
                    continue;
                }
                $tieneValores = true;
                if (!is_numeric($valor)) {
                    $numerica = false;
                    break;
                }
                $numerica = true;
            }

            if ($this->esIdentificador((string)$col)) {
                $numerica = false;
            }

            $aligns[] = ($tieneValores && $numerica) ? 'right' : 'left';
        }

        return $aligns;
    }

    private function formatearValor($valor, string $columna): string
    {
        if (is_array($valor)) {
            return (string)json_encode($valor, JSON_UNESCAPED_UNICODE);
        }

        $texto = trim((string)$valor);
        if ($texto === '' || !is_numeric($texto)) {
            return $texto;
        }

        if ($this->esIdentificador($columna)) {
            return $texto;
        }

        $numero = (float)$texto;

        if ($this->esMoneda($columna)) {
            return '$' . number_format($numero, 2, ',', '.');
        }

        if (abs($numero - round($numero)) < 0.00001) {
            return number_format($numero, 0, ',', '.');
        }

        return number_format($numero, 2, ',', '.');
    }

    private function esMoneda(string $columna): bool
    {
        return (bool)preg_match('/total|costo|precio|venta|valor|monto|importe|subtotal|ingreso/i', $columna);
    }

    private function esIdentificador(string $columna): bool
    {
        return (bool)preg_match('/^(id|c[oó]digo|n[°º]?\s*orden|orden|a[nñ]o)$/iu', trim($columna));
    }

    // ------------------------------------------------------------------
    // Texto y primitivas de dibujo
    // ------------------------------------------------------------------

    private function text(float $x, float $baseline, string $texto, float $size, bool $bold, array $color): void
    {
        if ($texto === '') {
            return;
        }

        $this->c($this->color($color) . " rg\n");
        $this->c(sprintf(
            "BT /F%d %s Tf %s %s Td (%s) Tj ET\n",
            $bold ? 2 : 1,
            $this->num($size * self::UNITS),
            $this->num($x),
            $this->num($this->pageHeight - $baseline),
            $this->escape($texto)
        ));
    }

    private function fillRect(float $x, float $y, float $w, float $h, array $color): void
    {
        $this->c($this->color($color) . " rg\n");
        $this->c(sprintf(
            "%s %s %s %s re f\n",
            $this->num($x),
            $this->num($this->pageHeight - ($y + $h)),
            $this->num($w),
            $this->num($h)
        ));
    }

    private function line(float $x1, float $y1, float $x2, float $y2, array $color, float $width = 0.25): void
    {
        $this->c($this->color($color) . " RG\n");
        $this->c($this->num($width) . " w\n");
        $this->c(sprintf(
            "%s %s m %s %s l S\n",
            $this->num($x1),
            $this->num($this->pageHeight - $y1),
            $this->num($x2),
            $this->num($this->pageHeight - $y2)
        ));
    }

    private function color(array $rgb): string
    {
        return $this->num((float)$rgb[0]) . ' ' . $this->num((float)$rgb[1]) . ' ' . $this->num((float)$rgb[2]);
    }

    /**
     * Ancho estimado de un texto en milímetros (Helvetica).
     */
    private function anchoTexto(string $texto, float $size, bool $bold = false): float
    {
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }

        $em = 0.0;
        $caracteres = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($caracteres as $ch) {
            if ($ch === ' ') {
                $em += 0.28;
            } elseif (str_contains("ilI.,:;!|'`", $ch)) {
                $em += 0.28;
            } elseif (str_contains('mwMW@%&', $ch)) {
                $em += 0.82;
            } elseif (preg_match('/[A-ZÁÉÍÓÚÑÜ0-9]/u', $ch)) {
                $em += 0.60;
            } else {
                $em += 0.50;
            }
        }

        if ($bold) {
            $em *= 1.06;
        }

        return $em * $size * self::UNITS;
    }

    /**
     * Divide un texto en líneas que caben en el ancho indicado.
     *
     * @return string[]
     */
    private function envolver(string $texto, float $maxWidth, float $size, bool $bold = false): array
    {
        $maxWidth = max(4, $maxWidth);
        $texto = str_replace(["\r\n", "\r"], "\n", $texto);
        $lineas = [];

        foreach (explode("\n", $texto) as $parrafo) {
            $parrafo = trim($parrafo);
            $palabras = preg_split('/\s+/u', $parrafo, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (!$palabras) {
                $lineas[] = '';
                continue;
            }

            $linea = '';
            foreach ($palabras as $palabra) {
                $candidato = $linea === '' ? $palabra : $linea . ' ' . $palabra;

                if ($this->anchoTexto($candidato, $size, $bold) <= $maxWidth) {
                    $linea = $candidato;
                    continue;
                }

                if ($linea !== '') {
                    $lineas[] = $linea;
                    $linea = '';
                }

                while ($this->anchoTexto($palabra, $size, $bold) > $maxWidth && mb_strlen($palabra) > 1) {
                    $corte = mb_strlen($palabra) - 1;
                    while ($corte > 1 && $this->anchoTexto(mb_substr($palabra, 0, $corte), $size, $bold) > $maxWidth) {
                        $corte--;
                    }
                    $lineas[] = mb_substr($palabra, 0, $corte);
                    $palabra = mb_substr($palabra, $corte);
                }

                $linea = $palabra;
            }

            $lineas[] = $linea;
        }

        return $lineas ?: [''];
    }

    private static function filaPlana(array $fila): array
    {
        $plana = [];
        foreach ($fila as $valor) {
            $plana[] = is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string)$valor;
        }
        return $plana;
    }

    private function c(string $linea): void
    {
        $this->pages[count($this->pages) - 1] .= $linea;
    }

    private function maxWidth(): float
    {
        return $this->pageWidth - $this->marginLeft - $this->marginRight;
    }

    private function escape(string $texto): string
    {
        $texto = self::utf8ToWinAnsi($texto);
        $texto = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
        return $texto;
    }

    private static function utf8ToWinAnsi(string $texto): string
    {
        if (!mb_check_encoding($texto, 'UTF-8')) {
            return $texto;
        }
        return mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');
    }

    private function num(float $valor): string
    {
        return rtrim(rtrim(sprintf('%.3F', $valor), '0'), '.');
    }
}
