<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Comunidad;
use App\Models\Jugador;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers, SendsPasswordResetEmails, ResetsPasswords;
    protected $redirectTo = '/login/reset-password';
    protected $redirectAfterLogout = '/login';
    protected $guard = 'players';
    protected $loginView = 'app.login';
    protected $username = 'email';

    public function __construct()
    {
        $this->middleware('guest:players', ['except' => ['logout'] ]);
    }

    protected function showLoginForm()
    {
        $Model = Comunidad::where('principal', true)->first();
        if($Model != null) {
            return view($this->loginView, ['Model' => $Model]);
        }
        abort(404);
    }

    protected function login(Request $request)
    {
        $this->validateLogin($request);

        $credentials = $this->getCredentials($request);
        $player = Jugador::where('email', $request->email)->first();

        if ($player)
        {
            Session::put('isFirstSession', $player->isFirstSession);

            $request->merge(['redirectPath' => $player->isFirstSession ? "/login/reset-password" : $this->redirectTo]);

            if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
                return $this->handleUserWasAuthenticated($request, null);
            }
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function guard()
    {
        return Auth::guard($this->guard);
    }

    protected function broker()
    {
        return Password::broker($this->guard);
    }

    public function showLinkRequestForm()
    {
        return view('app.email.password');
    }

    public function showResetForm(Request $request)
    {
        return view('app.password')->with(['token' => $request->token, 'email' => $request->email]);
    }
}
