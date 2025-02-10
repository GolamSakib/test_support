<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Session;

class ReactRequestHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        header("Access-Control-Allow-origin: *");
        $headers = [
            'Access-Control-Allow-Methods' => 'POST,GET, OPTIONS, PUT,DELETE',
            'Access-Control-Allow-Headers' => "Content-Type, X-Auth-Token, origin, Authorization",
        ];
        if ($request->getMethod() === "OPTIONS") {
            return response()->json("OK", 200, $headers);
        }
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }


        return $response;
    }
}
