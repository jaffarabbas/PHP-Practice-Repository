<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the request has a valid API key in the header.
     * Expected header: X-API-KEY: your-secret-api-key
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from request header
        $apiKey = $request->header('X-API-KEY');

        // Define valid API key (in production, store this in .env file)
        $validApiKey = env('API_KEY', 'your-secret-api-key');

        // Check if API key is missing
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide X-API-KEY header.'
            ], 401);
        }

        // Check if API key is invalid
        if ($apiKey !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 403);
        }

        // API key is valid, continue to the next request
        return $next($request);
    }
}
