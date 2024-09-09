<?php

namespace App\Http\Middleware;

use App\Models\App;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthRouteAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $roles = $request->route()->getAction('roles', []);
        if (in_array(Auth::guard('web')->user()->perfil->nombre, (array)$roles)) {
            return $next($request);
        }

        return Auth::guard('web')->user()->perfil_id == App::$PERFIL_ADMINISTRADOR ? redirect(route('auth.home.index')) : redirect(route('auth.rankings.index'));

        //return redirect(route('index'));
    }
}
