<?php

namespace App\Http\Traits;

/**
 * Trait PDFPagesCount.
 *
 * @package App\Models\Traits
 */
trait PdfPagesCount
{
    /**
     * Detect number of pages in PDF file
     *
     * @param string $PDFContent
     *
     * @return int
     */
    public static function getNumPagesInPDF($PDFContent)
    {
        if (!$PDFContent) {
            return 0;
        }

        $firstValue = 0;
        $secondValue = 0;
        if (preg_match("/\/N\s+([0-9]+)/", $PDFContent, $matches)) {
            $firstValue = $matches[1];
        }

        if (preg_match_all("/\/Count\s+([0-9]+)/s", $PDFContent, $matches)) {
            $secondValue = max($matches[1]);
        }

        $pagesCount = (($secondValue != 0) ? $secondValue : max($firstValue, $secondValue));

        return (int) $pagesCount;
    }
}
