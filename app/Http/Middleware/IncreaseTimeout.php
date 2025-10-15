<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncreaseTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Increase PHP execution time limits for file operations
        set_time_limit(600); // 10 minutes

        // Increase memory limit if needed
        ini_set('memory_limit', '512M');

        return $next($request);
    }
}

