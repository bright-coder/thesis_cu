<?php

namespace App\Http\Middleware;

use Closure;

class CheckIsJson
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
        if (!$request->isJson()) {
            return response()->json(['msg'=>'Request must be json.'], 400);
        }
        return $next($request);
    }
}
