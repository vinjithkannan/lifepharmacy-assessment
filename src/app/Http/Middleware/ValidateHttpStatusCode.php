<?php

namespace App\Http\Middleware;

use Closure;

class ValidateHttpStatusCode
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!method_exists($response, 'getStatusCode')) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 100 || $statusCode > 599) {
            return response()->json([
                'error' => 'Invalid server response',
                'original_status' => $statusCode
            ], 500);
        }

        return $response;
    }
}
