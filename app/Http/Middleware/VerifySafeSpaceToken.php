<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySafeSpaceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        $expectedToken = env('SAFE_SPACE_BOT_TOKEN');

        if (!$expectedToken) {
            return response()->json([
                'success' => false, 
                'message' => 'API token is not configured on the server.'
            ], 500);
        }

        if ($token !== $expectedToken) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
