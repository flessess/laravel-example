<?php

namespace App\Api\OcrElastic;

interface OcrElasticApiInterface
{
    public function getDatapointsCount($fGuid, $dataOwnerId);
}
