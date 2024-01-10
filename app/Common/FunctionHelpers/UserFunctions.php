<?php

namespace App\Common\FunctionHelpers;

use App\Common\ResponseFormatter;
use App\Common\UUID;
use App\Constants;
use App\Jobs\SendMail;
use App\Models\FireGuardLicense;
use App\Models\PasswordReset;
use App\Models\RefreshToken;
use App\Models\StateLicense;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFunctions
{

    public static function authenticateUser($id, $user): bool
    {
        $user = FireGuardLicense::where("user_id", $user)
            ->where("id", $id)
            ->first();
        if ($user) {
            return (true);
        } else {
            return (false);
        }
    }

    public static function addUser($request, $role_id)
    {
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
        } else {
            return ($newUser);
        }
    }

    public static function editUser($request, $user_id): \Illuminate\Http\JsonResponse
    {
        $updateUser = User::where("id", $user_id)->first();
        if ($updateUser->email !== $request->input("email")) {
            $updateUser->email = $request->input("email");
            $updateUser->active = 0;
        }
        $updateUser->first_name = $request->input("first_name");
        $updateUser->last_name = $request->input("last_name");
        $updateUser->phone_no = $request->input("phone_number");
        $updateUser->update();

        if (!$updateUser->save()) {
            return ResponseFormatter::errorResponse();
        } else {
            return ($updateUser);
        }
    }

    public static function verifyRequest($request, $role_id)
    {
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
            SendMail::dispatch($request->input("email"), $token, $siteName, $request->input("first_name"), $role_id);
            return (true);
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

    public static function getUser($users): array
    {
        $contentData = [];
        foreach ($users as $user) {
            if ($user->active == 0) {
                if ($user->status == 0)
                    $active = "Pending";
                else
                    $active = "Inactive";
            } else
                $active = "Active";
            $contentData[] = [
                "user_id" => $user->id,
                "user_first_name" => $user->first_name,
                "user_last_name" => $user->last_name,
                "user_email" => $user->email,
                "user_status" => $user->status,
                "is_active" => $active,
            ];
        }
        return $contentData;
    }

    public static function getProfileDetailsUser($user_id, $userProfile): array
    {
        $license = array();
        $contentData = array();
        $s3SiteName = Config::get('constants.s3_bucket');
        $stateLicenses = StateLicense::where("user_id", $user_id)->get();
        foreach ($stateLicenses as $stateLicense) {
            $fireGuard = array();
            $fireGuardLicenses = FireGuardLicense::
            where("user_id", $user_id)
                ->where("state_id", $stateLicense->state_id)
                ->get();
            foreach ($fireGuardLicenses as $fireGuardLicense) {
                $fireGuard[] = [
                    "fire_guard_license_id" => $fireGuardLicense->id,
                    "fire_guard_license_type" => $fireGuardLicense->fire_guard_license_type,
                    "fire_guard_license_type_name" => $fireGuardLicense->fire_arms->name,
                    "fire_guard_license_image" => $s3SiteName . $fireGuardLicense->fire_guard_license_image,
                    "fire_guard_license_expiry" => $fireGuardLicense->fire_guard_license_expiry,
                ];
            }
            $license[] = [
                "state_id" => $stateLicense->state_id,
                "state_name" => $stateLicense->state->name,
                "security_guard_license_image" => $s3SiteName . $stateLicense->security_guard_license_image,
                "security_guard_license_expiry" => $stateLicense->security_guard_license_expiry,
                "cpr_certificate_image" => $s3SiteName . $stateLicense->cpr_certificate_image,
                "cpr_certificate_expiry" => $stateLicense->cpr_certificate_expiry,
                "fire_guard" => $fireGuard
            ];
        }
        $contentData = [
            "user_id" => $userProfile->user_id,
            "user_first_name" => $userProfile->user->first_name,
            "user_last_name" => $userProfile->user->last_name,
            "user_email" => $userProfile->user->email,
            "user_phone_no" => $userProfile->user->phone_no,
            "user_street" => $userProfile->address1,
//                "user_address_2" => $userProfile->address2,
            "user_state_id" => $userProfile->user->state_id,
            "user_state_name" => $userProfile->user->state->name,
            "user_city" => $userProfile->city,
            "user_zipcode" => $userProfile->zipcode,
            "user_profile_image" => $userProfile->profile_image == null ? "" : $s3SiteName . $userProfile->profile_image,
            "user_ssc_image" => $userProfile->ssc_image == null ? "" : $s3SiteName . $userProfile->ssc_image,
            "user_govt_id_image" => $userProfile->govt_id_image == null ? "" : $s3SiteName . $userProfile->govt_id_image,
            "user_govt_id_expiry_date" => $userProfile->govt_id_expiry_date,
            "user_osha_license_type" => $userProfile->osha_license_type,
            "user_osha_license_image" => $userProfile->osha_license_image == null ? "" : $s3SiteName . $userProfile->osha_license_image,
            "user_osha_license_expiry_date" => $userProfile->osha_license_expiry_date,
            "user_account_number" => $userProfile->account_number,
            "user_routing" => $userProfile->routing,
            "user_bank_name" => $userProfile->bank_name,
            "user_terms_and_condition" => $userProfile->terms_and_condition,
            "state_license" => $license,
        ];
        return $contentData;
    }

    public static function getProfileDetailsCustomer($userProfile): array
    {
        $contentData = array();
        $s3SiteName = Config::get('constants.s3_bucket');

        $contentData = [
            "web_user_id" => $userProfile->user_id,
            "web_first_name" => $userProfile->user->first_name,
            "web_last_name" => $userProfile->user->last_name,
            "web_email" => $userProfile->user->email,
            "web_phone_no" => $userProfile->user->phone_no,
            "web_address_1" => $userProfile->address1,
            "web_address_2" => $userProfile->address2,
            "web_state" => $userProfile->user->state_id,
            "web_city" => $userProfile->city,
            "web_zipcode" => $userProfile->zipcode,
            "web_profile_image" => $s3SiteName . $userProfile->profile_image,
            "web_state_id_image" => $s3SiteName . $userProfile->state_id_image,
        ];

        return $contentData;
    }
}
