<?php

namespace NazmulHasan\SmartQueryOptimizer\Middleware;

use Closure;

class QueryMonitorMiddleware
{
    public function handle($request, Closure $next)
    {
        app('query-collector')->start();
        return $next($request);
    }
}
