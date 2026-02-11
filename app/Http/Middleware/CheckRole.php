<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INSUFFICIENT_PERMISSIONS',
                        'message' => 'Vous n\'avez pas les permissions necessaires pour cette action.',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ], 403);
            }

            abort(403, 'Vous n\'avez pas les permissions necessaires pour cette action.');
        }

        return $next($request);
    }
}
