<?php

namespace Control\Auth\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

use Control\Common\Controller;
use Control\Infrastructure\User;
use Control\Common\Traits\HttpErrorResponseTrait;

class PasswordResetLinkController extends Controller
{
    use HttpErrorResponseTrait;

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users'],
        ], [
            'email.required' => 'E-postadressen er obligatorisk.',
            'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
            'email.exists' => 'Ingen konto er knyttet til den oppgitte e-postadressen.',
        ]);

        $user = User::where('email', '=', $request->email)->first();
        if ($user->blocked) {
            return $this->responseFailure(
                status: Response::HTTP_FORBIDDEN,
                detail: 'Brukeren er sperret.'
            );
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
