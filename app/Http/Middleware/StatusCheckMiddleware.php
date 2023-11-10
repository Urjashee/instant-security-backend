<?php

namespace App\Http\Middleware;

use App\Common\ResponseFormatter;
use App\Constants;
use Closure;
use Illuminate\Http\Request;

class StatusCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$status)
    {

        if (in_array($request->input(Constants::CURRENT_PROFILE_STATUS_KEY), $status))
            return $next($request);
        else
            return ResponseFormatter::unauthorizedResponse( "Unauthorized action!");
        //return response()->json(["success" => false, "status" => "error", "message" => "Unauthorized action!"], 401);

    }
}
