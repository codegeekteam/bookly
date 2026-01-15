<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class CheckBlockedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        // Check the `is_blocked` field in the related tables
        if (
            ($user->customer && $user->customer->is_blocked) ||
            ($user->serviceProvider && $user->serviceProvider->is_blocked) ||
            ($user->employee && $user->employee->is_blocked)
        ) {
            return response()->json(['message' => __('Your account is blocked, please contact admin')], 401);
        }

        return $next($request);
    }
}
