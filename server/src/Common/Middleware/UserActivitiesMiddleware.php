<?php

namespace Control\Common\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class UserActivitiesMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $payload = $request->collect();
            if (isset($payload['password'])) {
                $payload['password'] = null;
            }

            if (isset($payload['password_confirmation'])) {
                $payload['password_confirmation'] = null;
            }

            Log::channel('gelf')->info('User activity', [
                'request_ip_address' => $request->ip(),
                'route_name' => $request->route()->getName(),
                'payload' => $payload->toJson(),
                'user_id' => $user->id,
            ]);
        }

        return $next($request);
    }
}
