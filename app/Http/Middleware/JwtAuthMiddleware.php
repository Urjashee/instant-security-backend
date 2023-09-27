<?php

namespace App\Http\Middleware;

use App\Common\ResponseFormatter;
use App\Constants;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->bearerToken() == null)
            return ResponseFormatter::errorResponse( 'Token missing in header!');
            //return response()->json(["success" => false, "status" => "error", "message" => "Token missing in header!"]);
        $signer = new Sha512();
        $token = (new Parser())->parse((string)$request->bearerToken());
        $extra = [];
        if ($token->verify($signer, Config::get("jwt.secret")))
            return $next($request->merge(array_merge([
                Constants::CURRENT_USER_ID_KEY => $token->getClaim("user_id"),
                Constants::CURRENT_EMAIL_KEY => $token->getClaim("email"),
                Constants::CURRENT_FIRST_NAME_KEY => $token->getClaim("firstname"),
                Constants::CURRENT_LAST_NAME_KEY => $token->getClaim("lastname"),
                Constants::CURRENT_ROLE_ID_KEY => $token->getClaim("role_id"),
                Constants::CURRENT_ROLE_NAME_KEY => $token->getClaim("role_name"),
            ], $extra)));
        else
            return ResponseFormatter::errorResponse( 'Invalid token!');
            //return response()->json(["success" => false, "status" => "error", "message" => "Invalid token!"]);
    }
}
