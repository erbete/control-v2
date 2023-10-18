<?php

namespace Control\Auth\Http;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

use Control\Common\Controller;
use Control\Infrastructure\User;
use Control\Common\Traits\HttpErrorResponseTrait;

class NewPasswordController extends Controller
{
    use HttpErrorResponseTrait;

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users'],
            'password' => ['required', 'confirmed', 'min:12', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'token.required' => 'Tilgang nektet.',
            'email.required' => 'E-postadressen er obligatorisk.',
            'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
            'email.exists' => 'Ingen konto er knyttet til den oppgitte e-postadressen.',

            'password.required' => 'Passord er obligatorisk.',
            'password.confirmed' => 'Passordbekreftelsen er ikke lik.',
            'password.min' => 'Passord må inneholde minst 12 tegn.',
            'password.regex' => 'Passordkrav: minst 1 liten bokstav, 1 stor bokstav, 1 tall og 1 symbol.',
        ]);

        $user = User::where('email', '=', $request->email)->first();
        if ($user->blocked) {
            return $this->responseFailure(
                status: Response::HTTP_FORBIDDEN,
                detail: 'Brukeren er sperret.'
            );
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function () use (&$user, &$request) {
                $user->forceFill(['password' => $request->password])->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
