<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            if ($request->user()->role === UserRole::ADMIN) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}
