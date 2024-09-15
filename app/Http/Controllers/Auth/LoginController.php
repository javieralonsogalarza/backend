<?php

namespace App\Http\Controllers\Auth;

use App\Models\App;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    use AuthenticatesUsers, SendsPasswordResetEmails, ResetsPasswords;

    protected $redirectTo = '/auth/home';
    protected $redirectAfterLogout = '/auth/login';
    protected $loginView = 'auth.login';
    protected $username = 'email';

    public function __construct()
    {
        $this->middleware('guest:web', ['except' => ['logout'] ]);
    }

    protected function login(Request $request)
    {
        $this->validateLogin($request);

        $credentials = $this->getCredentials($request);
        $usuario = User::where('email', $request->email)->first();

        if ($usuario) {

            $request->merge([
                'redirectPath' => $usuario->perfil_id == App::$PERFIL_ADMINISTRADOR ? "/auth/home" : "/auth/rankings"
            ]);

            if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
                return $this->handleUserWasAuthenticated($request, null);
            }
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function guard()
    {
        return Auth::guard('web');
    }

    protected function broker()
    {
        return Password::broker('users');
    }

    public function showLinkRequestForm()
    {
        return view('app.email.resetear-password');
    }

    public function showResetForm(Request $request)
    {
        return view('app.resetear-password')->with(['token' => $request->token, 'email' => $request->email]);
    }
}
