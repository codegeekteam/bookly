<?php

namespace App\Http\Middleware;

use App\Traits\HandleResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    use HandleResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\HandleResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $request->header('lang')? app()->setLocale($request->header('lang')):app()->setLocale('en');
        return $next($request);
    }
}
