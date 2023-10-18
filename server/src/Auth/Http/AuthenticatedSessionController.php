<?php

namespace Control\Auth\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use Control\Common\Controller;
use Control\Auth\Requests\LoginRequest;
use Control\Common\Traits\HttpErrorResponseTrait;

class AuthenticatedSessionController extends Controller
{
    use HttpErrorResponseTrait;

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse|Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->blocked) {
            $this->destroy($request);
            return $this->responseFailure(
                status: Response::HTTP_FORBIDDEN,
                detail: 'Brukeren er sperret.'
            );
        }

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
