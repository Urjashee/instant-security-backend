<?php

namespace App\Http\Middleware;

use App\Common\ResponseFormatter;
use App\Constants;
use Closure;
use Illuminate\Http\Request;

class CheckCurrentUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$user_id)
    {

        if (in_array($request->input(Constants::CURRENT_USER_ID_KEY), $user_id))
            return $next($request);
        else
            return ResponseFormatter::unauthorizedResponse( "Unauthorized action!");
        //return response()->json(["success" => false, "status" => "error", "message" => "Unauthorized action!"], 401);

    }
}
