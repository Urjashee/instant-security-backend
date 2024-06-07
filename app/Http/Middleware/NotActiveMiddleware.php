<?php

namespace App\Http\Middleware;

use App\Common\ResponseFormatter;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;

class NotActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() == null)
            return ResponseFormatter::errorResponse('Token missing in header!');
        $signer = new Sha512();
        $token = (new Parser())->parse((string)$request->bearerToken());
        $extra = [];
        if ($token->verify($signer, Config::get("jwt.secret"))) {
            $user = User::where("id", $token->getClaim("user_id"))->first();
            if ($user->active) {
                return $next($request);
            } else {
                return ResponseFormatter::forbiddenResponse('User needs to be logged out');
            }
        }
    }
}
