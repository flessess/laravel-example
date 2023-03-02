<?php

namespace App\Api\Ocr;

class OcrApiDummy implements OcrApiInterface
{
    public function __construct(private $uploadCalledTimes = 0)
    {
    }

    public function getPagesList($fGuid, $dataOwnerId)
    {
        return [];
    }

    public function getPageContent($path, $dataOwnerId)
    {
        return 0;
    }

    public function uploadFile($file, $fGuid, $dataOwnerId)
    {
        ++$this->uploadCalledTimes;

        return [];
    }

    public function getUploadCalledTimes(): int
    {
        return $this->uploadCalledTimes;
    }
}
