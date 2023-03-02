<?php

namespace App\Api\OcrElastic;

use App\Helpers\RequestHelper;
use Exception;
use GuzzleHttp\Client;

class OcrElasticApi implements OcrElasticApiInterface
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * OcrApi constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getDatapointsCount($fGuid, $dataOwnerId)
    {
        try {
            $response = RequestHelper::retry(
                function () use ($fGuid, $dataOwnerId) {
                    return $this->client->post(
                        "v1/datapoints/counter_by_file?api_key=" . config('ocr-elastic-api.api-key'),
                        [
                            'json' => [
                                'fguid' => $fGuid,
                            ],
                            'headers' => [
                                'X-DATA-OWNER' => $dataOwnerId,
                            ]
                        ]
                    );
                }
            );

            return $this->handleResponse($response)['data']['all_fields_count'] ?? 0;
        } catch (Exception $e) {
            logException($e);
            return 0;
        }
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    private function handleResponse($response)
    {
        $content = (string)$response->getBody()->getContents();

        $json = json_decode($content, true);

        $jsonLastError = json_last_error();

        if ($jsonLastError === JSON_ERROR_NONE) {
            return $json;
        }

        throw new \RuntimeException('Invalid JSON response from server: ' . $jsonLastError);
    }
}
