<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectDeletedUserSession
{
    /**
     * Redirect sessions whose user record no longer exists before protected views render.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $guard = Auth::guard('web');
        $sessionKey = $guard->getName();

        if (! $request->session()->has($sessionKey)) {
            return $next($request);
        }

        if (User::whereKey($request->session()->get($sessionKey))->exists()) {
            return $next($request);
        }

        if ($request->routeIs('session.deleted', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'La cuenta asociada a esta sesión ya no existe.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->route('session.deleted');
    }
}
