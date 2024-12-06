<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Application Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Handle API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = 500;
            $response = [
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ];

            // Authentication Exceptions
            if ($e instanceof AuthenticationException) {
                $status = 401;
                $response['error'] = 'Unauthenticated';
            }
            // Validation Exceptions
            else if ($e instanceof ValidationException) {
                $status = 422;
                $response['error'] = 'Validation Error';
                $response['errors'] = $e->errors();
            }
            // HTTP Exceptions
            else if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
                $response['error'] = 'HTTP Error';
            }

            // Add debug information if in debug mode
            if (config('app.debug')) {
                $response['debug'] = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }

            return response()->json($response, $status);
        }

        // Handle web requests normally
        return parent::render($request, $e);
    }
}
