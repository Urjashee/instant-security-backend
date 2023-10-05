<?php

namespace App\Common\FunctionHelpers;

use App\Models\CustomerProfile;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserProfiles;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use function config;

class JwtHelper
{
    public static function generateRefreshToken($uuid): \Lcobucci\JWT\Token
    {
        $signer = new Sha512();
        return $refreshToken = (new Builder())
            ->setIssuedAt(time())
            ->set("uuid", $uuid)
            ->set("token_expiry", Carbon::now()->addDays(30)->toDateTimeString())
            ->sign($signer, config("jwt.secret"))
            ->getToken();
    }

    public static function generateAccessToken(User $user): \Lcobucci\JWT\Token
    {
        $builder = new Builder();
        $signer = new Sha512();
        $builder
            ->setIssuedAt(time())
            ->set("user_id", $user->id)
            ->set("email", $user->email)
            ->set("firstname", $user->first_name)
            ->set("lastname", $user->last_name)
            ->set("role_id", $user->role->id)
            ->set("role_name", $user->role->name)
            ->set("profile_exist", $user->profile);
        return $token = $builder
            ->sign($signer, Config::get("jwt.secret"))
            ->getToken();
    }
}
