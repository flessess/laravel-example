<?php

namespace App\Http\Controllers\Api\V1;

use App\Mail\FileReceivedMail;
use App\Services\FileUploaderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes\Post;
use Ramsey\Uuid\Uuid;
use Sxope\Facades\SxopeLogger;
use Sxope\Http\Attributes\Schemas\Responses\Error403Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;

class EmailController extends BaseController
{
    /**
     * @param Request $request
     * @param FileUploaderService $fileUploaderService
     * @return JsonResponse
     */
    #[Post(
        path: '/api/v1/email/receive',
        operationId: 'email-receive',
        description: "Once Sendgrid receives an email, it will call on this method with an object containing data, including attachment file paths. In order for this to work, you will need to add a valid domain to your Sendgrid account , and points it's webhook domain to this method. See https://sendgrid.com/docs/for-developers/parsing-email/setting-up-the-inbound-parse-webhook/",
        summary: 'Main Sendgrid inbound parsing method',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Sendgrid receive email'],
        responses: [
            new Error403Response(),
            new Error500Response(),
        ]
    )]
    public function receive(Request $request, FileUploaderService $fileUploaderService): JsonResponse
    {
        $data = $request->all();
        SxopeLogger::addLabels([
            'action_type' => 'files',
            'action' => 'cache.email',
        ]);

        SxopeLogger::beginProfile($token = 'Handle email file');
        if (!isset($data['inbound_parse_key']) || trim($data['inbound_parse_key']) != trim(config('services.sendgrid.inbound-parse-key'))) {
            SxopeLogger::error($errorMessage = 'Sendgrid request failed token validation');
            logException(new Exception($errorMessage));

            return $this->respondNotAllowed(['Please provide a valid access token in the Sendgrid Account']);
        }

        if ($data['attachments'] === 0) {
            SxopeLogger::warning($errorMessage = 'No attachments found in this email');

            return $this->respondSuccess([
                'message' => $errorMessage,
            ]);
        }

        $operationId = Uuid::uuid4()->toString();

        $metadata = [
            'email' => $data['from'],
            'email_subject' => $data['subject'],
        ];

        $filesData = [];
        for ($i = 1; $i <= $data['attachments']; $i++) {
            try {
                $filesData[] = $fileUploaderService->upload($data['attachment' . $i], $operationId, 'EMAIL', $metadata);
            } catch (Exception $e) {
                SxopeLogger::error($errorMessage = $e->getMessage(), $metadata);
                logException(new Exception($errorMessage));
                return $this->respondError(['Cannot process your file', $e->getMessage(), $e->getLine()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        Mail::to(extractEmail($data['from']))->send(new FileReceivedMail());

        SxopeLogger::endProfile(
            $token,
            [
                'files' => $filesData,
            ]
        );

        return $this->respondSuccess([
            'message' => 'File(s) received',
        ]);
    }
}
