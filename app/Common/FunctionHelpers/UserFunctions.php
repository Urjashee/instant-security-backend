<?php

namespace App\Common\FunctionHelpers;

use App\Common\ResponseFormatter;
use App\Common\UUID;
use App\Jobs\SendMail;
use App\Models\PasswordReset;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFunctions
{
    public static function addUser($request, $role_id) {
        $newUser = new User;
        $newUser->email = $request->input("email");
        $newUser->first_name = $request->input("first_name");
        $newUser->last_name = $request->input("last_name");
        $newUser->phone_no = $request->input("phone_number");
        $newUser->password = Hash::make($request->input("password"));
        $newUser->user_role_id = $role_id;
        $newUser->active = 0;
        $newUser->state_id = $request->input("state");
        $newUser->save();
        $newUser->refresh();

        $updateFriendlyName = User::where("id", $newUser->id)->first();
        $updateFriendlyName->friendly_name = str_pad($newUser->id, 2, '0', STR_PAD_LEFT) . "_" .
            $request->input("first_name") . "_" .
            $request->input("last_name");
        $updateFriendlyName->update();

        if (!$newUser->save()) {
            return ResponseFormatter::errorResponse();
        } else{
            return($newUser);
        }
    }

    public static function verifyRequest($request, $role_id) {
        $siteName = Config::get('constants.url');

        $token = Str::random(64);
        $newPassword = new PasswordReset();
        $newPassword->email = $request->input("email");
        $newPassword->token = $token;
        $newPassword->active_token = 0;
        $newPassword->type = 1;
        $newPassword->created_at = Carbon::now();
        $newPassword->save();

        if ($newPassword->save()) {
            SendMail::dispatch($request->input("email"), $token, $siteName, $request->input("first_name"),$role_id);
            return(true);
        }
    }

    public static function generateToken($user): array
    {
        $token = JwtHelper::generateAccessToken($user);
        $uuid = UUID::v4();
        $refreshToken = JwtHelper::generateRefreshToken($uuid);
        $rt = new RefreshToken();
        $rt->user_id = $user->id;
        $rt->uuid = $uuid;
        $rt->hash = Hash::make((string)$refreshToken);
        $rt->save();
        if ($rt->save()) {
            return [$token, $refreshToken];
        }
    }
}
