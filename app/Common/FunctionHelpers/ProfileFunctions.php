<?php

namespace App\Common\FunctionHelpers;


use Illuminate\Support\Facades\Storage;

class ProfileFunctions
{
    public static function addUpdateProfile($userProfile, $request) {
        if ($request->has('user_profile_image')) {
            $profileImageFileName = time() . '.' . $request->file('user_profile_image')->getClientOriginalExtension();
            $profile_image = $request->file("user_profile_image");
            $profile_image->storeAs('user_profile_image', $profileImageFileName, 's3');
            $userProfile->profile_image = 'user_profile_image/' . $profileImageFileName;
        }
        if ($request->has('user_ssc_image')) {
            $sscImageFileName = time() . '.' . $request->file('user_ssc_image')->getClientOriginalExtension();
            $ssc_image = $request->file("user_ssc_image");
            $ssc_image->storeAs('user_ssc_image', $sscImageFileName, 's3');
            $userProfile->ssc_image = 'user_ssc_image/' . $sscImageFileName;
        }

        if ($request->has('user_govt_id_image')) {
            $govtIdImageFileName = time() . '.' . $request->file('user_govt_id_image')->getClientOriginalExtension();
            $govt_id_image = $request->file("user_govt_id_image");
            $govt_id_image->storeAs('user_govt_id_image', $govtIdImageFileName, 's3');
            $userProfile->govt_id_image = 'user_govt_id_image/' . $govtIdImageFileName;
        }

        if ($request->has('govt_id_expiry_date')) {
            $userProfile->govt_id_expiry_date = $request->input("govt_id_expiry_date");
        }

        if ($request->has('osha_image')) {
            $oshaImageFileName = time() . '.' . $request->file('osha_image')->getClientOriginalExtension();
            $osha_image = $request->file("osha_image");
            $osha_image->storeAs('osha_image', $oshaImageFileName, 's3');
            $userProfile->osha_license_image = 'osha_image/' . $oshaImageFileName;
        }

        if ($request->has('osha_license_type')) {
            $userProfile->osha_license_type = $request->input("osha_license_type");
        }
        if ($request->has('osha_expiry_date')) {
            $userProfile->osha_license_expiry_date = $request->input("osha_expiry_date");
        }
        return $userProfile->update();
    }

    public static function addUpdateStateLicenses($userProfile, $request) {

        return $userProfile->update();
    }

    public static function convertImage($value) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pin = mt_rand(1000000, 9999999)
            . mt_rand(1000000, 9999999)
            . $characters[rand(0, strlen($characters) - 1)];
        $extensions = '';
        $image = $value->fire_guard_license_image;
        if (str_contains($image,'png')) $extensions = 'png';
        if (str_contains($image,'jpeg')) $extensions = 'jpeg';
        if (str_contains($image,'bmp')) $extensions = 'bmp';
        if (str_contains($image,'gif')) $extensions = 'gif';

        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace('data:image/bmp;base64,', '', $image);
        $image = str_replace('data:image/gif;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $fireGuardLicenseFileName = 'fire_guard_license_image/' . $pin . '.' . $extensions;
        Storage::disk('s3')->put($fireGuardLicenseFileName, base64_decode($image));
        return $fireGuardLicenseFileName;
    }
}
