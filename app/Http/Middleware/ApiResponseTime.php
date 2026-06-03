<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $executionTime = round((microtime(true) - $start) * 1000, 2);

        \Log::info('API Response Time', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'response_time_ms' => $executionTime
        ]);

        return $response;
    }
}
