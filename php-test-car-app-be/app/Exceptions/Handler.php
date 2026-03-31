<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json(
                [
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ],
                422
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage() !== '' ? $exception->getMessage() : 'Request failed.';

            return response()->json(
                [
                    'message' => $message,
                ],
                $statusCode
            );
        }

        return response()->json(
            [
                'message' => $exception->getMessage() ?: 'Server error',
            ],
            method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500
        );
    }
}
