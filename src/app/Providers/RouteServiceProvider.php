<?php

namespace App\Providers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends  ServiceProvider
{
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Correct rate limiter for products
        RateLimiter::for('products', function (Request $request) {
            return Limit::perMinute(30) // Allow 30 requests per minute
            ->by($request->user()?->id ?: $request->ip());
        });
    }
}
