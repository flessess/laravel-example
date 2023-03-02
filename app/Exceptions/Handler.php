<?php

namespace App\Exceptions;

use App\Services\OpenTelemetryService;
use App\Helpers\GoogleCloudErrorReportingHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Sxope\Contracts\SxopeExceptionInterface;
use Sxope\Contracts\SxopeNotFoundExceptionInterface;
use Sxope\Exceptions\SxopeDomainException;
use Sxope\Exceptions\SxopeEntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        SxopeDomainException::class,
        SxopeEntityNotFoundException::class,
        SxopeExceptionInterface::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // notify newrelic
            if (!app()->environment(['local']) && extension_loaded('newrelic')) { // Ensure PHP agent is available
                newrelic_notice_error($e);
            }
            // notify gcloud
            GoogleCloudErrorReportingHelper::logException($e);

            OpenTelemetryService::logException($e);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $requestUserAgent = $request->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);
        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() === 404 && $isDP) {
                // not found
                return redirect()->route('home', $request->all());
            }
        }

        if ($exception instanceof SxopeExceptionInterface) {
            return $this->respondError([$exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } elseif ($exception instanceof UuidExceptionInterface) {
            return $this->respondError([$exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } elseif ($exception instanceof SxopeDomainException) {
            return $this->respondError([$exception->getMessage()], $exception->getCode());
        } elseif ($exception instanceof SxopeNotFoundExceptionInterface) {
            return $this->respondError([$exception->getMessage()], Response::HTTP_NOT_FOUND);
        } elseif (
            $exception instanceof AuthenticationException
            || $exception instanceof ValidationException
            || $exception instanceof HttpExceptionInterface
            || $exception instanceof Responsable
            || $exception instanceof ModelNotFoundException
        ) {
            return parent::render($request, $exception);
        }
        if ($request->expectsJson()) {
            return $this->respondError(
                env('APP_ENV') === 'production' ? ['Server error'] : ['Server error', $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return parent::render($request, $exception);
    }
    /**
     * @param array $errors
     * @param int $errorCode
     *
     * @return JsonResponse
     */
    protected function respondError(array $errors, int $errorCode): JsonResponse
    {
        return response()->json(
            [
                'code' => $errorCode,
                'status' => 'error',
                'errors' => $errors,
            ],
            $errorCode
        );
    }
}
