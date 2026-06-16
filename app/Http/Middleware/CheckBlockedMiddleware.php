<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->estBloque()) {
            // Revoke current session token to enforce logout
            $user->tokens()->delete();

            $message = 'Votre compte est suspendu ou desactivé.';
            if ($user->bloque_jusqua && $user->bloque_jusqua->isFuture()) {
                $message = 'Votre compte est suspendu jusqu\'au ' . $user->bloque_jusqua->toDateTimeString() . '.';
            }

            return response()->json([
                'message' => $message
            ], 403);
        }

        return $next($request);
    }
}
