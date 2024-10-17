<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

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
        //
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

   public function report(Throwable $exception)
    {
        // استخدم Log لتسجيل الخطأ بمستويات مختلفة بناءً على نوع الاستثناء
        if ($exception instanceof NotFoundHttpException) {
            Log::warning('Resource not found: ' . $exception->getMessage());
        } elseif ($exception instanceof AccessDeniedHttpException) {
            Log::warning('Access denied: ' . $exception->getMessage());
        } else {
            Log::error($exception->getMessage(), ['exception' => $exception]);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception): JsonResponse
    {
       
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found',
                'error' => $exception->getMessage(),
            ], 404);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json([
                'message' => 'Access denied',
                'error' => $exception->getMessage(),
            ], 403);
        }


        return response()->json([
            'message' => 'Something went wrong',
            'error' => $exception->getMessage(),
        ], 500);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
