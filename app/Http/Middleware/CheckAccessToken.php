<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

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
        
        $token = $request->bearerToken();
        if (User::where('accessToken','=',$token)->first() === null) {
            return response()->json(['msg'=>'Bad token.'],401);
        }

        return $next($request);
    }
}
