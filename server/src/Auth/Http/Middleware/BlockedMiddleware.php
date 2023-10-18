<?php

namespace Control\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

use Control\Common\Traits\HttpErrorResponseTrait;

class BlockedMiddleware
{
    use HttpErrorResponseTrait;

    public function handle($request, Closure $next)
    {
        if ($request->user()->blocked) {
            return $this->responseFailure(Response::HTTP_FORBIDDEN, 'Brukeren er sperret.');
        }

        return $next($request);
    }
}
