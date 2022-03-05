<?php

namespace App\Concerns;

use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait HandleApiException
{
    /**
     * Render the exception as a JSON Response
     *
     * @param \Throwable $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderApiException(Throwable $exception): JsonResponse
    {
        $responseData = $this->prepareApiExceptionData($exception);
        $payload = Arr::except($responseData, 'statusCode');
        $statusCode = $responseData['statusCode'];

        return response()->json($payload, $statusCode);
    }

    /**
     * Generate the status code, message and data for a particular exception
     *
     * @param Throwable $exception
     *
     * @return array
     */
    public function prepareApiExceptionData(Throwable $exception): array
    {
        $responseData['status'] = false;
        $message = $exception->getMessage();

        if ($exception instanceof NotFoundHttpException) {
            $responseData['message'] = empty($message) ? "Resource not found" : $message;
            $responseData["statusCode"] = 404;
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $responseData['message'] = empty($message) ? "Request method not allowed" : $message;
            $responseData['statusCode'] = 405;
        } elseif ($exception instanceof ModelNotFoundException) {
            $responseData['message'] = "Resource not found";
            $responseData['statusCode'] = 404;
        } elseif ($exception instanceof AuthenticationException) {
            $responseData['message'] = $message ?? "Unauthenticated";
            $responseData['statusCode'] = 401;
        } elseif ($exception instanceof ValidationException) {
            $responseData['message'] = $message;
            $responseData['errors'] = $exception->validator->errors();
            $responseData['statusCode'] = 422;
        } else {
            $responseData['message'] = !$this->isHttpException($exception) ? "Server error" : $exception->getMessage();
            $responseData['statusCode'] = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;
            if ($debug = $this->extractExceptionData($exception)) {
                $responseData['debug'] = $debug;
            }
        }

        return $responseData;
    }

    /**
     * Extraction of the exception details.
     *
     * @param Throwable $exception
     *
     * @return array
     */
    public function extractExceptionData(Throwable $exception): array
    {
        if (config('app.debug') && !$this->isHttpException($exception)) {
            $data = [
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ];
        } else {
            $data = [];
        }

        return $data;
    }
}
