<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFirebaseApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('firebase.api_key');

        if ($configuredKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $providedKey = $request->bearerToken()
            ?: $request->header('X-Firebase-Api-Key');

        if (! is_string($providedKey) || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
