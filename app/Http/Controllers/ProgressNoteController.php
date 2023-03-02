<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FiveStarApi;
use App\Traits\SxopeApiIdHashTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Inventcorp\SxopeApiGatewayClient\Api\SXOPEAPILoginApi;
use Inventcorp\SxopeApiGatewayClient\Api\UserServiceV1FullDataTypesApi;
use Inventcorp\SxopeApiGatewayClient\ApiException;
use Inventcorp\SxopeApiGatewayClient\Configuration;

/**
 * Class ProgressNoteController
 *
 * @package App\Http\Controllers
 */
class ProgressNoteController extends Controller
{
    use SxopeApiIdHashTrait;

    public function snapshot($progressNoteId)
    {
        $pageTitle = 'Progress Note Snapshot';

        $baseApiUrl = config('five-star-api.host') . '/api';

        $apiConfig = (new Configuration())
            ->setHost($baseApiUrl);

        $guzzleParams = [
            'verify' => config('app.verify_service_ssl'),
        ];

        $client = new SXOPEAPILoginApi(new Client($guzzleParams), $apiConfig);

        try {
            $sessionParamName = config('sso.session_token_param_name');
            $userAuthenticationToken = \Request::session()->get($sessionParamName);

            $response = $client->loginSsoAuthSsoLogin(
                config('five-star-api.key'),
                $userAuthenticationToken
            );
        } catch (ApiException $exception) {
            // TODO need to add implementation
            logException($exception, 'error on calling sxope-api-php-sdk : ' . $exception->getMessage());
            throw $exception;
        }

        if($response->getCode() == 200) {
            $data = $response->getData();

            $apiToken = $data->getApiToken();
        }


        $baseApiUrl = config('five-star-api.web-host') . '/api';

        return view(
            'progress-note.snapshot',
            compact(
                'pageTitle',
                'apiToken',
                'baseApiUrl'
            )
        )
            ->with('progressNoteId', $progressNoteId)
            ->with('user', auth()->user());
    }

    public function tabs($progressNoteId, $tabName) {
        return $this->snapshot($progressNoteId);
    }

    public function subtabs($progressNoteId, $tabName, $subTabName) {
        return $this->snapshot($progressNoteId);
    }
}
