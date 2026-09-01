<?php

namespace App\Core;

/**
 * Generador mínimo de PDF válido en PHP puro (sin librerías externas).
 *
 * Produce un PDF con texto y una tabla sencilla, con soporte básico para
 * caracteres Latin-1. Es suficiente para reportes de negocio simples.
 */
class PdfBuilder
{
    private float $pageWidth;
    private float $pageHeight;
    private float $marginLeft;
    private float $marginTop;
    private float $maxWidth;
    private float $cursorX;
    private float $cursorY;
    private int $page;
    private string $content = '';
    private float $fontSize;

    private const UNITS = 0.352778; // 1 punto = 0.352778 mm aprox.

    public function __construct()
    {
        $this->pageWidth  = 210;
        $this->pageHeight = 297;
        $this->marginLeft = 15;
        $this->marginTop  = 20;
        $this->maxWidth   = $this->pageWidth - $this->marginLeft * 2;
        $this->cursorX    = $this->marginLeft;
        $this->cursorY    = $this->marginTop;
        $this->page       = 1;
        $this->fontSize   = 11;
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
        $alto = $size * 0.55;
        if ($this->cursorY + $alto > $this->pageHeight - 25) {
            $this->newPage();
        }
        $state = $bold ? ['F1' => 1] : [];
        $this->content .= sprintf(
            "BT /F%d %s Tf %s Td (%s) Tj ET\n",
            $bold ? 2 : 1,
            $this->num($size * self::UNITS),
            $this->num($this->cursorX) . ' ' . $this->num($this->cursorY),
            $this->escape($texto)
        );
        $this->cursorY -= $alto + 3;
        $this->cursorX = $this->marginLeft;
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
        $altura = max(array_map('count', $lineas)) * ($this->fontSize * 0.5) + $pad * 2;
        $altoMax = $this->fontSize * 0.55;

        if ($this->cursorY - $altura < 25) {
            $this->newPage();
        }

        $filaBottom = $this->cursorY - $altura;

        // Borde de la celda
        $x = $this->marginLeft;
        foreach ($valores as $idx => $valor) {
            $this->rect($x, $filaBottom, $colWidth, $altura);
            $x += $colWidth;
        }

        // Texto
        $tx = $this->marginLeft + $pad;
        $ty = $this->cursorY - $pad - $altoMax;
        foreach ($lineas as $idx => $lineasTexto) {
            $ly = $ty;
            $lx = $this->marginLeft + $pad;
            foreach ($lineasTexto as $linea) {
                $this->content .= sprintf(
                    "BT /F%d %s Tf %s Td (%s) Tj ET\n",
                    $header ? 2 : 1,
                    $this->num($this->fontSize * self::UNITS),
                    $this->num($lx) . ' ' . $this->num($ly),
                    $this->escape($linea)
                );
                $ly -= $altoMax;
                $lx = $tx;
            }
            $tx += $colWidth;
            $ty = $this->cursorY - $pad - $altoMax;
        }

        $this->cursorY = $filaBottom - $this->fontSize * 0.2;
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
        $this->content .= sprintf("%s %s %s %s re S\n", $this->num($x), $this->num($y), $this->num($w), $this->num($h));
    }

    private function newPage(): void
    {
        $this->page++;
        $this->content .= "Q\n";
        $this->content .= sprintf("0.5 0.5 0.5 rg\n");
        $this->content .= sprintf("BT /F1 8 Tf %s Td (Pagina %d) Tj ET\n", $this->num($this->marginLeft) . ' ' . $this->num($this->pageHeight - 15), $this->page);
        $this->content .= "0 0 0 rg\n";
        $this->content .= "1 0 0 1 0 0 cm\n";
        $this->content .= sprintf("q\n");
        $this->cursorY = $this->pageHeight - 30;
        $this->cursorX = $this->marginLeft;
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
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $this->num($this->pageWidth) . " " . $this->num($this->pageHeight) . "] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents " . 6 . " 0 R >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $objects[6] = "<< /Length " . strlen($this->content) . " >>\nstream\n" . $this->content . "endstream";

        $pdf  = "%PDF-1.4\n";
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
