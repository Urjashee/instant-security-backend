<?php

namespace App\Common\FunctionHelpers;

use App\Constants;
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
        $s3SiteName = Config::get('constants.s3_bucket');
        $image = null;
        if ($user->role->id == Constants::MOBILE_USER) {
            $user_profile = UserProfile::where("user_id",$user->id)->first();
            $image = $user_profile->profile_image == null ? "" : $s3SiteName . $user_profile->profile_image;
        } if ($user->role->id == Constants::WEB_USER) {
            $user_profile = CustomerProfile::where("user_id",$user->id)->first();
            $image = $user_profile->profile_image == null ? "" : $s3SiteName . $user_profile->profile_image;
        }
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
            ->set("user_status", $user->status)
            ->set("is_profile", $user->profile)
            ->set("friendly_name", $user->friendly_name)
            ->set("profile_image", $image)
            ->set("profile_web_status", $user->active);
        return $token = $builder
            ->sign($signer, Config::get("jwt.secret"))
            ->getToken();
    }
}
