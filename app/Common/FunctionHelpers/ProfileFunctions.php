<?php

namespace App\Common\FunctionHelpers;


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
}
