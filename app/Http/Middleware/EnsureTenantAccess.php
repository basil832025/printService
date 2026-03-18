<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user || ! $user->is_active || ! $user->tenant_id || ! $user->tenant?->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Ваш аккаунт неактивен или не привязан к клиенту.',
            ]);
        }

        return $next($request);
    }
}
