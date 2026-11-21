<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentDiscoveryHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Thêm Link header trỏ tới auth.md và api-catalog
        $response->headers->set(
            'Link', 
            '</.well-known/api-catalog>; rel="api-catalog", </auth.md>; rel="service-doc"'
        );

        return $response;
    }
}