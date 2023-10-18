<?php

namespace Control\Auth\Http;

use Illuminate\Support\Facades\Auth;

use Control\Common\Controller;
use Control\Infrastructure\User;
use Control\Common\Mappers\UserMapper;

class AuthController extends Controller
{
    public function user()
    {
        return response()->json(UserMapper::map(User::find(Auth::id())));
    }
}
