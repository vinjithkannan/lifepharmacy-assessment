<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimeoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Set maximum execution time for this request
        set_time_limit(600); // 5 minutes

        try {
            return $next($request);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Maximum execution time')) {
                return response()->json([
                    'error' => 'Request timeout',
                    'message' => 'The request took too long to process'
                ], 504);
            }

            throw $e;
        }
    }
}
