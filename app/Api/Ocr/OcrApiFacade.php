<?php

namespace App\Api\Ocr;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getPagesList($fGuid, $dataOwnerId)
 * @method static getPageContent($path, $dataOwnerId)
 * @method static uploadFile($file, $fGuid, $dataOwnerId)
 */
class OcrApiFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return OcrApiInterface::class;
    }
}
