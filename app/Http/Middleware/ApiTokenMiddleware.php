<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fixedToken = 'T0NMSURfMjAyNV9VUExPQURFUl9UT0tFTg==';

        // Get the token from the Authorization header
        $headerToken = $request->header('Authorization');

        // Check if the token matches (Allow both "Bearer token" and direct token)
        if (!$headerToken || $headerToken !== "Bearer $fixedToken") {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
