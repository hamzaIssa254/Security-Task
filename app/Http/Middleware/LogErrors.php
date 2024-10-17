<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), ['exception' => $exception]);
            
            return response()->json([
                'message' => 'An error occurred',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
