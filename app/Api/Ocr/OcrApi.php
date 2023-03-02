<?php

namespace App\Api\Ocr;

use App\Helpers\RequestHelper;
use Exception;
use GuzzleHttp\Client;

/**
 * Class PersonaApi.
 *
 * @package App\Services
 */
class OcrApi implements OcrApiInterface
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

    /**
     * @param $fGuid
     * @param $dataOwnerId
     * @return mixed
     * @throws Exception
     */
    public function getPagesList($fGuid, $dataOwnerId)
    {
        $response = RequestHelper::retry(
            function() use ($fGuid, $dataOwnerId) {
                return $this->client->get(
                    "v4/chart/images/$fGuid/list",
                    [
                        'headers' => [
                            'X-DATA-OWNER' => $dataOwnerId,
                        ]
                    ]
                );
            }
        );

        return $this->handleResponse($response);
    }

    /**
     * @param $path
     * @return mixed
     *
     * @throws Exception
     */
    public function getPageContent($path, $dataOwnerId)
    {
        $response = RequestHelper::retry(
            function () use ($path, $dataOwnerId) {
                return $this->client->post(
                    ltrim($path, '/api/'),
                    [
                        'headers' => [
                            'X-DATA-OWNER' => $dataOwnerId,
                        ]
                    ]
                );
            }
        );

        return $response->getBody()->getContents();
    }

    /**
     * @param $file
     * @param $fGuid
     * @param $dataOwnerId
     * @return mixed
     * @throws Exception
     */
    public function uploadFile($file, $fGuid, $dataOwnerId)
    {
        $response = RequestHelper::retry(
            function () use ($file, $fGuid, $dataOwnerId) {
                return $this->client->post('v2/chart/upload',
                    [
                        'multipart' => [
                            [
                                'name' => 'file',
                                'contents' => $file,
                                'filename' => $fGuid,
                            ],
                        ],
                        'headers' => [
                            'Connection' => 'close',
                            'X-DATA-OWNER' => $dataOwnerId,
                        ],
                        'curl' => [
                            CURLOPT_FORBID_REUSE => true,
                            CURLOPT_FRESH_CONNECT => true,
                        ],
                    ]
                );
            }
        );

        return $this->handleResponse($response);
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
