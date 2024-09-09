<?php

namespace App\Http\Middleware;

use App\Models\App;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if($guard == 'web'){
                    return Auth::guard('web')->user()->perfil_id == App::$PERFIL_ADMINISTRADOR ? redirect(route('auth.home.index')) : redirect(route('auth.rankings.index'));
                }else if($guard == 'players'){
                    return Auth::guard('players')->user()->isFirstSession ? redirect(route('app.showResetPassword')) : redirect(route('app.perfil.index'));
                }
            }
        }

        return $next($request);
    }
}
