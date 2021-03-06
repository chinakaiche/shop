<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
class CheckIfEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->user()->email_verified){
            return redirect(route('email_verify_notice'));
        }
        return $next($request);
    }
}
