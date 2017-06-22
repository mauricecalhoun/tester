<?php

namespace Calhoun\AB\Middleware;

use Route;
use Closure;
use Illuminate\Session\Middleware\StartSession;

class ABTesting
{
    public function handle($request, Closure $next)
    {
        $reponse  = $this->applySessionMiddleware($request, $next);
        track($request->headers->get('referer'), $request->getPathInfo());
        return $reponse;
    }

    private function applySessionMiddleware($request, Closure $next)
    {
      if(collect(Route::gatherRouteMiddleware($request->route()))->contains(StartSession::class))
      {
        return $next($request);
      }

      return app(StartSession::class)->handle($request, function($request) use($next){
          return $next($request);
      });
    }
}
