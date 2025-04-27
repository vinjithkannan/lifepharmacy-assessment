<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks.
     */
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Resource not found',
                    'errors' => ['resource' => 'The requested resource does not exist']
                ], 404);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson()) {
                $status = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : 500;

                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ] : null
                ], $status);
            }
        });
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        // Ensure valid status code
        $statusCode = $response->getStatusCode();
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'error' => $e instanceof HttpException
                    ? $e->getMessage()
                    : 'Server Error',
                'status' => $statusCode
            ], $statusCode);
        }

        return $response;
    }
}
