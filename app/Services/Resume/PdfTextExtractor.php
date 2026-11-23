<?php

namespace App\Services\Resume;

use RuntimeException;

/**
 * Pulls the text out of a PDF resume. Needs: composer require smalot/pdfparser
 * Scanned PDFs (a picture of text) have no text to pull; those need OCR instead.
 */
class PdfTextExtractor
{
    public function extract(string $path): string
    {
        if (! class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new RuntimeException('PDF reading is not installed yet. Run: composer require smalot/pdfparser');
        }

        $text = (new \Smalot\PdfParser\Parser)->parseFile($path)->getText();

        return $this->normalise($text);
    }

    /**
     * Tidy extracted text: plain line breaks, plain "-" bullets, no ligatures or stray
     * control characters, no runs of blank lines.
     */
    public function normalise(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = strtr($text, [
            "\u{FB00}" => 'ff', "\u{FB01}" => 'fi', "\u{FB02}" => 'fl', "\u{FB03}" => 'ffi', "\u{FB04}" => 'ffl',
            "\u{00A0}" => ' ',
        ]);

        // Control characters other than newline and tab (form feeds, nulls...).
        $text = preg_replace('/[^\P{C}\n\t]/u', '', $text) ?? $text;

        // Bullet symbols at the start of a line become plain hyphens.
        $text = preg_replace('/^[ \t]*[\x{2022}\x{25CF}\x{25AA}\x{25E6}\x{25A0}\x{2023}\x{2219}\x{00B7}][ \t]*/mu', '- ', $text) ?? $text;

        $text = preg_replace('/[ \t]+\n/', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
