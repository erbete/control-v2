<?php

namespace Control\Common\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;

use Control\Infrastructure\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::toMailUsing(function (User $user, string $token) {
            return (new MailMessage)
                ->subject('Varsel om tilbakestilling av passord')
                ->view('mail.reset-password-link-email', [
                    'resetPwdUrl' =>  url(env('FRONTEND_URL')) . '/reset-password?token=' . $token . '&email=' . $user->email,
                ]);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . $user->email;
        });
    }
}
