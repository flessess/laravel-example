<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\File;
use App\Models\FileSource;
use App\Services\FileUploaderService;
use App\Services\SfaxService;
use Carbon\Carbon;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class SfaxmeController
 *
 * @package App\Http\Controllers\Api\V1
 */
class SfaxmeController extends BaseController
{
    /**
     * Open SSL encryption method to use.
     *
     * @var string
     */
    private const ENCRYPTION_METHOD = 'aes-256-cbc';

    /**
     * Numbers for which we can fetch faxes.
     * Downloading faxes from other numbers is not allowed.
     *
     * @var array
     */
    public const ALLOWED_NUMBERS = [
        '18889785655',
        '18889785669',
        '18334957205',
    ];

    public function __construct(private readonly SfaxService $sfaxService)
    {
    }

    public function get(Request $request, FileUploaderService $fileUploaderService)
    {
        if (!$this->tokenIsValid($request->input('token'))) {
            Log::debug('Sfaxme token failed validation');
            Log::debug($request);
            return $this->respondError(['Invalid Sfax Callback token'], 403);
        }

        $sfax = $this->sfaxService->buildClient();

        $faxId = $request->input('faxid');
        $from = $request->input('infromfaxnumber');
        $to = $request->input('intofaxnumber');

        if (!in_array($to, self::ALLOWED_NUMBERS)) {
            Log::debug('Received a fax from an unrelated number: ' . $to);
            Log::debug($request);
            return $this->respondSuccess(['OK']);
        }

        $result = $sfax->downloadInboundFaxAsPdf($faxId);

        if (is_object($result) && !$result->isSuccess) {
            Log::debug('Cannot download file from the SFaxMe server');
            Log::debug($result->message);
            return $this->respondError(['Cannot download file', $result->message, false,], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $filename = $faxId . '.pdf';

        $localpath = storage_path('tmp_sfaxme_files');

        if (!file_exists($localpath)) {
            mkdir($localpath, 0777, true);
        }

        $localpath .= '/' . $filename;

        file_put_contents($localpath, $result);

        $file = new UploadedFile($localpath, $filename, 'application/pdf', false, false);

        $operationId = Uuid::uuid4()->toString();

        $metadata = [
            'sender_phone_number' => $from,
        ];

        try {
            $fileExistByBytes = File::query()
                ->where([
                    'file_size' => $file->getSize(),
                    'file_source_id' => FileSource::getIdByAlias(FileSource::SOURCE_FAX)
                ])
                ->whereBetween(
                    'uploaded_at',
                    [
                        Carbon::now()->subMinutes(2),
                        Carbon::now()->addMinute(),
                    ]
                )
                ->count();

            if ($fileExistByBytes) {
                logSfaxmeData([
                    'sender_phone_number' => $from,
                    'file_name' => $filename,
                    'file_size' => $file->getSize(),
                ]);
                return $this->respondSuccess(['File already exist!']);
            }

            $fileExistByHash = File::query()
                ->where([
                    'md5_checksum' => new Bytes(md5($result, true)),
                ])
                ->count();

            if ($fileExistByHash) {
                return $this->respondSuccess(['File already exist!']);
            }

            $fileUploaderService->upload($file, $operationId, 'FAX', $metadata);

            @unlink($localpath);
        } catch (\Exception $e) {
            logException($e);
            return $this->respondError(['Cannot process your file', $e->getMessage(), $e->getLine()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Log::debug('Successfully processed file from ' . $from);
        return $this->respondSuccess(['Added to upload queue']);
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private function tokenIsValid($token)
    {
        $valid = false;

        $token = str_replace(' ', '+', $token);

        $decrypt = openssl_decrypt(
            $token,
            self::ENCRYPTION_METHOD,
            config('app.sfaxme_api_encryption_key'),
            0,
            config('app.sfaxme_api_iv')
        );

        if ($decrypt) {
            parse_str('&' . $decrypt, $data);

            if (
                isset($data['Username']) && $data['Username'] === config('app.sfaxme_api_user')
                && isset($data['ApiKey']) && $data['ApiKey'] === config('app.sfaxme_api_key')
            ) {
                $valid = true;
            }
        }

        return $valid;
    }
}
