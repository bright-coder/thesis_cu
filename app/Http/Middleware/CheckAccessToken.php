<?php

namespace App\Http\Middleware;

use Closure;

class CheckAccessToken
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

        // if($request->accessToken !== "As24fkwxkeUopKn91sc") {
        //     return redirect('/');
        // }
        return $next($request);
    }
}
