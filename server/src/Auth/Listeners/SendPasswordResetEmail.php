<?php

namespace Control\Auth\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;

use Control\Auth\Mail\PasswordResetEmail;

class SendPasswordResetEmail
{
    /**
     * Handle the event.
     *
     * @param  PasswordReset  $event
     *
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $user = $event->user;
        Mail::to($user)->send(new PasswordResetEmail($user));
    }
}
