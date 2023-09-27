<?php

namespace App\Http\Middleware;

use App\Common\ResponseFormatter;
use App\Constants;
use Closure;
use Illuminate\Http\Request;

class RoleBasedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {

        if (in_array($request->input(Constants::CURRENT_ROLE_NAME_KEY), $roles))
            return $next($request);
        else
            return ResponseFormatter::unauthorizedResponse( "Unauthorized action!");
            //return response()->json(["success" => false, "status" => "error", "message" => "Unauthorized action!"], 401);

    }
}
