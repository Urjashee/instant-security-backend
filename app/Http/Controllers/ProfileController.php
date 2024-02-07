<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\JobFunctions;
use App\Common\FunctionHelpers\ProfileFunctions;
use App\Common\FunctionHelpers\UserFunctions;
use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\CustomerProfile;
use App\Models\FireGuardLicense;
use App\Models\State;
use App\Models\StateLicense;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Exception;

class ProfileController extends Controller
{
    public function filledProfileList(Request $request): \Illuminate\Http\JsonResponse
    {
        $contentData = array();
        $profile_image_details = 0;
        $personal_details = 0;
        $state_license = 0;
        $personal_payment = 0;
        $document = 0;
        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        $stateLicense = StateLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        $fireLicenses = FireGuardLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($userProfile) {
            if (
                $userProfile->profile_image != null
            ) {
                $profile_image_details = 1;
            }
            if (
                $userProfile->ssc_image != null &&
                $userProfile->govt_id_image != null &&
                $userProfile->govt_id_expiry_date != null
            ) {
                $personal_details = 1;
            }
            if (
                $userProfile->account_number != null &&
                $userProfile->routing != null
            ) {
                $personal_payment = 1;
            }
            if (
                $userProfile->terms_and_condition == 1
            ) {
                $document = 1;
            }
            if ($stateLicense) {
                if (
                    $stateLicense->security_guard_license_image != null &&
                    $stateLicense->security_guard_license_expiry != null &&
                    $fireLicenses->fire_guard_license_type != null &&
                    $fireLicenses->fire_guard_license_image != null &&
                    $fireLicenses->fire_guard_license_expiry != null &&
                    $stateLicense->cpr_certificate_image != null &&
                    $stateLicense->cpr_certificate_expiry != null
                ) {
                    $state_license = 1;
                }
            }
            $contentData = [
                "is_profile_image" => $profile_image_details,
                "is_personal_ids" => $personal_details,
                "is_state_license" => $state_license,
                "is_payment_profile" => $personal_payment,
                "is_document" => $document,
            ];

            return ResponseFormatter::successResponse("Profile Check list", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Profile not found");
        }
    }

    public function editUserProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "phone_number" => "required",
            "street" => "required",
            "city" => "required",
            "zipcode" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        if (!State::where("id", $request->input("state"))
            ->where("active", 1)->first())
            return ResponseFormatter::errorResponse("Not an active state");

        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            try {
                DB::beginTransaction();

                $user->phone_no = $request->input("phone_number");
                $user->state_id = $request->input("state");
                $user->update();

                if ($request->has('user_profile_image')) {
                    if ($userProfile->profile_image != null) {
                        $s3 = Storage::disk('s3');
                        $s3->delete($userProfile->profile_image);
                    }
                    $profileImageFileName = time() . '.' . $request->file('user_profile_image')->getClientOriginalExtension();
                    $profile_image = $request->file("user_profile_image");
                    $profile_image->storeAs('user_profile_image', $profileImageFileName, 's3');
                    $userProfile->profile_image = 'user_profile_image/' . $profileImageFileName;
                }
                $userProfile->address1 = $request->input("street");
                if ($request->has("address2")) {
                    $userProfile->address2 = $request->input("address2");
                }
                $userProfile->city = $request->input("city");
                $userProfile->zipcode = $request->input("zipcode");
                $userProfile->update();
                DB::commit();
                return ResponseFormatter::successResponse("Personal info updated");
            } catch (\Exception $exception) {
                DB::rollback();
                return ResponseFormatter::errorResponse("Error in profile updating", $exception);
            }

        } else {
            return ResponseFormatter::errorResponse("No such user profile");
        }
    }

    public function editProfileImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "user_profile_image" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            if ($userProfile->profile_image != null) {
                $s3 = Storage::disk('s3');
                $s3->delete($userProfile->profile_image);
            }
            $profileImageFileName = time() . '.' . $request->file('user_profile_image')->getClientOriginalExtension();
            $profile_image = $request->file("user_profile_image");
            $profile_image->storeAs('user_profile_image', $profileImageFileName, 's3');
            $userProfile->profile_image = 'user_profile_image/' . $profileImageFileName;
            $userProfile->update();
            return ResponseFormatter::successResponse("Profile image updated");
        } else {
            return ResponseFormatter::errorResponse("No such user profile");
        }
    }

    public function addPersonal(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "govt_id_expiry_date" => "required",
            "user_govt_id_image" => "required",
            "user_ssc_image" => "required",
//            "user_profile_image" => "required"
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            ProfileFunctions::addUpdateProfile($userProfile, $request);

            return ResponseFormatter::successResponse("Personal info updated");
        } else {
            return ResponseFormatter::errorResponse("No such user profile");
        }
    }

    public function editPersonal(Request $request): \Illuminate\Http\JsonResponse
    {
        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            ProfileFunctions::addUpdateProfile($userProfile, $request);

            return ResponseFormatter::successResponse("Personal info updated");
        } else {
            return ResponseFormatter::errorResponse("No such user profile");
        }
    }

    public function addStateLicense(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "state_id" => "required",
            "security_guard_license_image" => "required",
            "security_guard_license_expiry" => "required",
            "cpr_certificate_image" => "required",
            "cpr_certificate_expiry" => "required"
        ]);


        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        if (!State::where("id", $request->input("state_id"))
            ->where("active", Constants::ACTIVE)->first())
            return ResponseFormatter::errorResponse("Not an active state");

        $getStateLicense = StateLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where("state_id", $request->input("state_id"))
            ->first();

        if ($getStateLicense) {
            return ResponseFormatter::errorResponse("State licenses already added");
        } else {
            $stateLicense = new StateLicense();
            $stateLicense->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
            $stateLicense->state_id = $request->input("state_id");

            if ($request->has('security_guard_license_image')) {
                $securityGuardLicenseFileName = time() . '.' . $request->file('security_guard_license_image')->getClientOriginalExtension();
                $security_guard_license_image = $request->file("security_guard_license_image");
                $security_guard_license_image->storeAs('security_guard_license_image', $securityGuardLicenseFileName, 's3');
                $stateLicense->security_guard_license_image = 'security_guard_license_image/' . $securityGuardLicenseFileName;
            }
            $stateLicense->security_guard_license_expiry = $request->input("security_guard_license_expiry");

            if ($request->input('fire_guard_license') !== null) {
                $arrayFireGuard = json_decode($request->input('fire_guard_license'));
                foreach ($arrayFireGuard as $value) {
                    $image = ProfileFunctions::convertImage($value);
                    $fire_guard_license = new FireGuardLicense();
                    $fire_guard_license->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
                    $fire_guard_license->state_id = $request->input("state_id");
                    $fire_guard_license->fire_guard_license_type = $value->fire_guard_license_type;
                    $fire_guard_license->fire_guard_license_image = $image;
                    $fire_guard_license->fire_guard_license_expiry = $value->fire_guard_license_expiry;
                    $fire_guard_license->save();
                }
            }

            if ($request->has('cpr_certificate_image')) {
                $cprCertificateFileName = time() . '.' . $request->file('cpr_certificate_image')->getClientOriginalExtension();
                $cpr_certificate_image = $request->file("cpr_certificate_image");
                $cpr_certificate_image->storeAs('cpr_certificate_image', $cprCertificateFileName, 's3');
                $stateLicense->cpr_certificate_image = 'cpr_certificate_image/' . $cprCertificateFileName;
            }
            $stateLicense->cpr_certificate_expiry = $request->input("cpr_certificate_expiry");
            $stateLicense->save();

            return ResponseFormatter::successResponse("State licenses added");
        }
    }

    public function editStateLicense(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "state_id" => "required",
        ]);

        $fireGuardLicenseList = array();

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        if (!State::where("id", $request->input("state_id"))
            ->where("active", Constants::ACTIVE)->first())
            return ResponseFormatter::errorResponse("Not an active state");

        $stateLicense = StateLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where("state_id", $request->input("state_id"))
            ->first();

        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if (!$stateLicense) {
            return ResponseFormatter::errorResponse("State licenses record doesn't exist");
        } else {

            if ($request->has('security_guard_license_image')) {
                $s3 = Storage::disk('s3');
                $s3->delete($stateLicense->security_guard_license_image);
                $securityGuardLicenseFileName = time() . '.' . $request->file('security_guard_license_image')->getClientOriginalExtension();
                $security_guard_license_image = $request->file("security_guard_license_image");
                $security_guard_license_image->storeAs('security_guard_license_image', $securityGuardLicenseFileName, 's3');
                $stateLicense->security_guard_license_image = 'security_guard_license_image/' . $securityGuardLicenseFileName;
            }
            if ($request->has('security_guard_license_expiry')) {
                $stateLicense->security_guard_license_expiry = $request->input("security_guard_license_expiry");
            }

            if ($request->has('cpr_certificate_image')) {
                $s3 = Storage::disk('s3');
                $s3->delete($stateLicense->cpr_certificate_image);
                $cprCertificateFileName = time() . '.' . $request->file('cpr_certificate_image')->getClientOriginalExtension();
                $cpr_certificate_image = $request->file("cpr_certificate_image");
                $cpr_certificate_image->storeAs('cpr_certificate_image', $cprCertificateFileName, 's3');
                $stateLicense->cpr_certificate_image = 'cpr_certificate_image/' . $cprCertificateFileName;
            }
            if ($request->has('cpr_certificate_expiry')) {
                $stateLicense->cpr_certificate_expiry = $request->input("cpr_certificate_expiry");
            }

            if ($request->has('fire_guard_license')) {
                $arrayFireGuard = json_decode($request->input('fire_guard_license'));
                foreach ($arrayFireGuard as $key => $value) {
                    $updateFireGuard =FireGuardLicense::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                        ->where("state_id", $request->input("state_id"))
                        ->where("fire_guard_license_type", $value->fire_guard_license_type)
                        ->first();
                    if ($updateFireGuard) {
                        $deleted_image = $updateFireGuard->fire_guard_license_image;
                        $image = ProfileFunctions::convertImage($value);
                        $updateFireGuard->fire_guard_license_image = $image;
                        $updateFireGuard->fire_guard_license_expiry = $value->fire_guard_license_expiry;
                        $updateFireGuard->update();
                        try {
                            $s3 = Storage::disk('s3');
                            $s3->delete($deleted_image);
                        } catch (\Exception $e) {
                            return ResponseFormatter::errorResponse($e->getMessage());
                        }
                        $fireGuardLicenseList[] = $updateFireGuard->id;
                    } else {
                        $image = ProfileFunctions::convertImage($value);
                        $fire_guard_license = new FireGuardLicense();
                        $fire_guard_license->user_id = $request->input(Constants::CURRENT_USER_ID_KEY);
                        $fire_guard_license->state_id = $request->input("state_id");
                        $fire_guard_license->fire_guard_license_type = $value->fire_guard_license_type;
                        $fire_guard_license->fire_guard_license_image = $image;
                        $fire_guard_license->fire_guard_license_expiry = $value->fire_guard_license_expiry;
                        $fire_guard_license->save();
                        $fire_guard_license->refresh();
                        $fireGuardLicenseList[] = $fire_guard_license->id;
                    }
                }
            }
            $stateLicense->update();
            $user->active = 0;
            $user->status = 0;
            $user->update();

            return ResponseFormatter::successResponse("State licenses added");
        }
    }

    public function deleteFireGuardLicense(Request $request, $id): \Illuminate\Http\JsonResponse {
        $auth_user = UserFunctions::authenticateUser($id, $request->input(Constants::CURRENT_USER_ID_KEY));
        if (!$auth_user)
            return ResponseFormatter::unauthorizedResponse("Unauthorized action!");

        $fireGuardLicense = FireGuardLicense::where('user_id', $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where('id', $id)
            ->first();

        if ($fireGuardLicense) {
            try {
                $s3 = Storage::disk('s3');
                $s3->delete($fireGuardLicense->fire_guard_license_image);
                $fireGuardLicense->delete();
            } catch (\Exception $e) {
                return ResponseFormatter::errorResponse($e->getMessage());
            }
            return ResponseFormatter::successResponse("Fire guard license successfully deleted");
        } else {
            return ResponseFormatter::errorResponse("Fire guard license could not be deleted");
        }
    }

    public function addBanking(Request $request): \Illuminate\Http\JsonResponse
    {
//        $validator = Validator::make($request->all(), [
//            "account_number" => "numeric",
//            "routing" => "required",
//        ]);
//
//        if ($validator->fails())
//            return ResponseFormatter::errorResponse($validator->errors()->first());

        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            $userProfile->account_number = $request->input("account_number");
            $userProfile->routing = $request->input("routing");
            $userProfile->update();

            return ResponseFormatter::successResponse("Banking info updated");
        } else {
            return ResponseFormatter::errorResponse("No such user profile");
        }
    }

    public function addDocument(Request $request): \Illuminate\Http\JsonResponse
    {
        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
            ->where("terms_and_condition", 1)
            ->first();

        if (!$userProfile) {
            $userProfileDocument = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
                ->first();
            $userProfileDocument->terms_and_condition = 1;
            $userProfileDocument->update();

            return ResponseFormatter::successResponse("Terms and Condition added");
        } else {
            return ResponseFormatter::errorResponse("Terms and Condition already updated");
        }
    }
    public function addSubmit(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::where("id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($user) {
            $user->status = 0;
            $user->active = 0;
            $user->profile = 1;
            $user->update();
            return ResponseFormatter::successResponse("User needs to logout");
        } else {
            return ResponseFormatter::errorResponse("NO such user found");
        }
    }

    public function getUserProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $userProfile = UserProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($userProfile) {
            $contentData = UserFunctions::getProfileDetailsUser($request->input(Constants::CURRENT_USER_ID_KEY),$userProfile);
            return ResponseFormatter::successResponse("User Profile", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Profile not found");
        }
    }


//    Web User
    public function editCustomerProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required",
            "first_name" => "required",
            "last_name" => "required",
            "phone_number" => "required",
            "address1" => "required",
            "city" => "required",
            "zipcode" => "numeric",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $customer = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($customer) {

//            UserFunctions::editUser($request,$request->input(Constants::CURRENT_USER_ID_KEY));
            $customer->address1 = $request->input("address1");
            if ($request->has("address2")) {
                $customer->address2 = $request->input("address2");
            }
            $customer->city = $request->input("city");
            $customer->zipcode = $request->input("zipcode");

            if ($request->has("profile_image")) {
                if ($customer->profile_image != null) {
                    $s3 = Storage::disk('s3');
                    $s3->delete($customer->profile_image);
                }
                $fileNameProfile = time() . '.' . $request->file('profile_image')->getClientOriginalExtension();
                $profile_images = $request->file("profile_image");
                $profile_images->storeAs('web_profile_images', $fileNameProfile, 's3');
                $customer->profile_image = 'web_profile_images/' . $fileNameProfile;
            }
            if ($request->has("state_id_image")) {
                $s3 = Storage::disk('s3');
                $s3->delete($customer->state_id_image);
                $fileNameState = time() . '.' . $request->file('state_id_image')->getClientOriginalExtension();
                $profile_images = $request->file("state_id_image");
                $profile_images->storeAs('web_state_id_images', $fileNameState, 's3');
                $customer->state_id_image = 'web_state_id_images/' . $fileNameState;
            }

            $customer->update();

            return ResponseFormatter::successResponse("User updated!");
        } else {
            return ResponseFormatter::errorResponse("No such user");
        }
    }

    public function getCustomerProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $userProfile = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($userProfile) {
            $contentData = UserFunctions::getProfileDetailsCustomer($userProfile);
            return ResponseFormatter::successResponse("Web Profile", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Profile not found");
        }
    }

    public function deleteStateLicense(Request $request, $state_id): \Illuminate\Http\JsonResponse
    {
        $s3 = Storage::disk('s3');
        $stateLicense = StateLicense::
        where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
//        where("user_id", 2)
            ->where("state_id", $state_id)
            ->first();

        if ($stateLicense) {
            $fireLicenses = FireGuardLicense::
            where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))
//            where("user_id", 2)
                ->where("state_id", $state_id)
                ->get();
            if ($fireLicenses) {
                foreach ($fireLicenses as $fireLicense) {
                    $s3->delete($fireLicense->fire_guard_license_image);
                    $fireLicense->delete();
                }
            }
            $s3->delete($stateLicense->security_guard_license_image);
            $s3->delete($stateLicense->cpr_certificate_image);
            $stateLicense->delete();
            return ResponseFormatter::successResponse("State license deleted ");
        } else {
            return ResponseFormatter::errorResponse("No tate licence found for this user with this state");
        }
    }
}
