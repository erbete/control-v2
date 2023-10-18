<?php

namespace Control\Auth\Http\Middleware;

use Illuminate\Http\Response;
use Closure;
use Control\Common\Traits\HttpErrorResponseTrait;

class RouteAccessPermissionMiddleware
{
    use HttpErrorResponseTrait;

    public function handle($request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            $userPermissions = $request->user()->permissions;
            foreach ($userPermissions as $up) {
                if ($up->slug === $permission) {
                    return $next($request);
                }
            }
        }

        return $this->responseFailure(Response::HTTP_FORBIDDEN, 'Brukeren har ugyldig tilgang.');
    }
}
