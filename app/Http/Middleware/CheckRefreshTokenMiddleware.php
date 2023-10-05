<?php

namespace App\Http\Middleware;

use App\Constants;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Closure;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;

class CheckRefreshTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->bearerToken() == null)
            return abort(401);
        $signer = new Sha512();
        $token = (new Parser(new JoseEncoder()))->parse((string)$request->bearerToken());
        $extra = [];
        if ($token->verify($signer, config("jwt.secret")) && $token->hasClaim("token_expiry") && $token->hasClaim("uuid")) {
            $expiry = Carbon::parse($token->getClaim('token_expiry'));
            if ($expiry->greaterThanOrEqualTo(Carbon::now()) && RefreshToken::where("uuid", $token->getClaim("uuid"))->first())
                return $next($request->merge(array_merge([
                    Constants::REFRESH_TOKEN_UUID_KEY => $token->getClaim("uuid")
                ], $extra)));
        }
        return abort(401);
    }
}
