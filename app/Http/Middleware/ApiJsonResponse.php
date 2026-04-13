<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Força o header Accept para application/json em requisições API
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
