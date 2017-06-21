<?php

namespace Calhoun\AB\Middleware;

use Closure;

class ABTesting
{
    public function handle($request, Closure $next)
    {
        $reponse  = $next($request);
        track($request->headers->get('referer'), $request->getPathInfo());
        return $reponse;
    }
}
