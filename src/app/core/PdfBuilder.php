<?php

namespace App\Core;

/**
 * Generador mínimo de PDF válido en PHP puro (sin librerías externas).
 *
 * Produce un PDF con texto y una tabla sencilla, con soporte básico para
 * caracteres Latin-1 y numeración de páginas. Es suficiente para reportes
 * de negocio simples.
 *
 * Coordenadas lógicas: origen arriba-izquierda, medidas en milímetros.
 * Internamente se traducen a las coordenadas ascendentes del PDF.
 */
class PdfBuilder
{
    private float $pageWidth;
    private float $pageHeight;
    private float $marginLeft;
    private float $marginTop;
    private float $maxWidth;
    private float $cursorY;
    private float $fontSize;

    /** @var string[] Contenido (stream) por página. */
    private array $pages;

    private const UNITS = 0.352778; // 1 punto = 0.352778 mm aprox.

    public function __construct()
    {
        $this->pageWidth  = 210;
        $this->pageHeight = 297;
        $this->marginLeft = 15;
        $this->marginTop  = 20;
        $this->maxWidth   = $this->pageWidth - $this->marginLeft * 2;
        $this->cursorY    = $this->marginTop;
        $this->fontSize   = 11;
        $this->pages      = ['']; // contenido de la página actual es el último elemento
    }

    /**
     * Añade una línea de texto.
     *
     * @param string $texto
     * @param float  $size
     * @param bool   $bold
     */
    public function addText(string $texto, float $size = 11, bool $bold = false): void
    {
        $this->fontSize = $size;
        $alto = $size * 0.55 + 3;
        if ($this->cursorY + $alto > $this->pageHeight - 25) {
            $this->newPage();
        }

        // y ascendente del PDF para una línea lógica "cursorY" (desde arriba)
        $py = $this->pageHeight - $this->cursorY;
        $this->c(sprintf(
            "BT /F%d %s Tf %s Td (%s) Tj ET\n",
            $bold ? 2 : 1,
            $this->num($size * self::UNITS),
            $this->num($this->marginLeft) . ' ' . $this->num($py),
            $this->escape($texto)
        ));
        $this->cursorY += $alto;
    }

    /**
     * Añade una tabla simple con encabezado y filas.
     *
     * @param array $columnas
     * @param array $filas
     */
    public function addTable(array $columnas, array $filas): void
    {
        $colWidth = $this->maxWidth / max(1, count($columnas));
        $cellPad  = 3;

        $this->drawRow($columnas, $colWidth, $cellPad, true);

        foreach ($filas as $fila) {
            $this->drawRow(self::filaPlana($fila), $colWidth, $cellPad, false);
        }
    }

    private function drawRow(array $valores, float $colWidth, float $pad, bool $header): void
    {
        $charWidth = $this->fontSize * self::UNITS * 0.5;
        $lineas = [];
        foreach ($valores as $idx => $valor) {
            $texto = (string)$valor;
            $maxChars = (int)floor(($colWidth - $pad * 2) / max(0.1, $charWidth));
            $lineas[$idx] = $this->envolver($texto, $maxChars);
        }
        $altoLinea = $this->fontSize * 0.5 + $pad;
        $altura = max(array_map('count', $lineas)) * $altoLinea + $pad;

        if ($this->cursorY + $altura > $this->pageHeight - 25) {
            $this->newPage();
        }

        $filaTop = $this->cursorY;

        // Borde de las celdas (rect con y ascendente del PDF)
        $x = $this->marginLeft;
        foreach ($valores as $idx => $valor) {
            $this->rect($x, $this->pageHeight - ($filaTop + $altura), $colWidth, $altura);
            $x += $colWidth;
        }

        // Texto: la primera línea se coloca cerca del borde superior de la celda
        $tx = $this->marginLeft + $pad;
        $ty = $filaTop + $pad + $this->fontSize * 0.55;
        foreach ($lineas as $idx => $lineasTexto) {
            foreach ($lineasTexto as $linea) {
                $this->c(sprintf(
                    "BT /F%d %s Tf %s Td (%s) Tj ET\n",
                    $header ? 2 : 1,
                    $this->num($this->fontSize * self::UNITS),
                    $this->num($tx) . ' ' . $this->num($this->pageHeight - $ty),
                    $this->escape($linea)
                ));
                $ty += $altoLinea;
            }
            $tx += $colWidth;
            $ty = $filaTop + $pad + $this->fontSize * 0.55;
        }

        $this->cursorY = $filaTop + $altura + $this->fontSize * 0.2;
    }

    private function envolver(string $texto, int $maxChars): array
    {
        if ($maxChars < 1) {
            $maxChars = 1;
        }
        $texto = trim($texto);
        $partes = [];
        while (mb_strlen($texto) > $maxChars) {
            $partes[] = mb_substr($texto, 0, $maxChars);
            $texto = mb_substr($texto, $maxChars);
        }
        $partes[] = $texto;
        return $partes;
    }

    private static function filaPlana(array $fila): array
    {
        $plana = [];
        foreach ($fila as $valor) {
            $plana[] = is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string)$valor;
        }
        return $plana;
    }

    private function rect(float $x, float $y, float $w, float $h): void
    {
        $this->c(sprintf("%s %s %s %s re S\n", $this->num($x), $this->num($y), $this->num($w), $this->num($h)));
    }

    private function newPage(): void
    {
        $this->sealCurrentPage();
        $this->pages[] = '';
        $this->cursorY = $this->marginTop;
    }

    private function sealCurrentPage(): void
    {
        $idx = count($this->pages) - 1;
        $this->pages[$idx] .= $this->pageFooter(count($this->pages));
    }

    private function pageFooter(int $pagina): string
    {
        return sprintf(
            "0.5 0.5 0.5 rg\nBT /F1 %s Tf %s Td (%s) Tj ET\n0 0 0 rg\n",
            $this->num(8 * self::UNITS),
            $this->num($this->marginLeft) . ' ' . $this->num(15),
            $this->escape('Página ' . $pagina)
        );
    }

    private function c(string $linea): void
    {
        $this->pages[count($this->pages) - 1] .= $linea;
    }

    private function escape(string $texto): string
    {
        $texto = self::utf8ToLatin1($texto);
        $texto = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
        return $texto;
    }

    private static function utf8ToLatin1(string $texto): string
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    private function num(float $valor): string
    {
        // Evita notación exponencial y usa punto decimal
        return rtrim(rtrim(sprintf('%.3F', $valor), '0'), '.');
    }

    /**
     * Devuelve el contenido binario del PDF.
     */
    public function output(): string
    {
        $this->sealCurrentPage();

        $n = count($this->pages);

        // Estructura de objetos:
        //   1 = Catalog, 2 = Pages
        //   páginas: 2*i+1, streams: 2*i+2  (i = 1..n)
        //   fuentes: después de todos los streams
        $fontReg = 2 + 2 * $n + 1;
        $fontBold = $fontReg + 1;

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        $kids = [];
        for ($i = 1; $i <= $n; $i++) {
            $kids[] = (2 + 2 * $i - 1) . ' 0 R';
        }
        $objects[2] = sprintf("<< /Type /Pages /Kids [%s] /Count %d >>", implode(' ', $kids), $n);

        for ($i = 1; $i <= $n; $i++) {
            $pageId = 2 + 2 * $i - 1;
            $contId = 2 + 2 * $i;
            $objects[$pageId] = sprintf(
                "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %s %s] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>",
                $this->num($this->pageWidth),
                $this->num($this->pageHeight),
                $fontReg,
                $fontBold,
                $contId
            );
            $objects[$contId] = "<< /Length " . strlen($this->pages[$i - 1]) . " >>\nstream\n" . $this->pages[$i - 1] . "endstream";
        }

        $objects[$fontReg] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[$fontBold] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";

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
}