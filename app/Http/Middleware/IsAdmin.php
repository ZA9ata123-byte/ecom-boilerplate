<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and if their role is 'admin'
        if ($request->user() && $request->user()->role === 'admin') {
            // If yes, allow the request to proceed
            return $next($request);
        }

        // If not, return a 'Forbidden' error
        return response()->json(['message' => 'This action is unauthorized.'], 403);
    }
}
