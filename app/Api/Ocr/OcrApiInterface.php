<?php

namespace App\Api\Ocr;

interface OcrApiInterface
{
    public function getPagesList($fGuid, $dataOwnerId);
    public function getPageContent($path, $dataOwnerId);
    public function uploadFile($file, $fGuid, $dataOwnerId);
}
