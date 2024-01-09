<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\TwillioHelper;
use App\Common\FunctionHelpers\UserFunctions;
use App\Constants;
use App\Common\ResponseFormatter;
use App\Jobs\SendMail;
use App\Models\CustomerProfile;
use App\Models\FireGuardLicense;
use App\Models\Roles;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    public function sendEmail(): \Illuminate\Http\JsonResponse
    {
//        SendMail::dispatch('urja@simpalm.com', 'token123', 'http://localhost:3000');
//        return ResponseFormatter::successResponse("User added!");
        try {
            TwillioHelper::sendSms();
        } catch (\Exception $e) {
            return ResponseFormatter::errorResponse($e->getMessage());
        }
        return ResponseFormatter::successResponse("SMS Sent!");
    }

    public function addNewUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "first_name" => "alpha",
            "last_name" => "alpha",
            "phone_number" => "required",
            "street" => "required",
            "state" => "numeric",
            "city" => "required",
            "zipcode" => "required",
            "password" => "min:8|alpha_num",
        ]);
        $siteName = Config::get('constants.url');
        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("email", $request->input("email"))->first();
        if ($user) {
            return ResponseFormatter::errorResponse('User already exists!');
        } else {
            $user = UserFunctions::addUser($request,3);

            $newUserProfile = new UserProfile();
            $newUserProfile->user_id = $user->id;
            $newUserProfile->address1 = $request->input("street");
            if ($request->has("address2")) {
                $newUserProfile->address2 = $request->input("address2");
            }

            $newUserProfile->city = $request->input("city");
            $newUserProfile->zipcode = $request->input("zipcode");
            $newUserProfile->save();

            UserFunctions::verifyRequest($request,3);
            return ResponseFormatter::successResponse("User added!");
        }
    }

    public function addNewCustomer(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "first_name" => "alpha",
            "last_name" => "alpha",
            "phone_number" => "required",
            "address1" => "required",
            "state" => "numeric",
            "city" => "required",
            "zipcode" => "numeric",
            "password" => "min:8|alpha_num",
            "state_id_image" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("email", $request->input("email"))->first();
        if ($user) {
            return ResponseFormatter::errorResponse('User already exists!');
        } else {

            $user = UserFunctions::addUser($request,2);

            $newCustomerProfile = new CustomerProfile();
            $newCustomerProfile->user_id = $user->id;
            $newCustomerProfile->address1 = $request->input("address1");
            if ($request->has("address2")) {
                $newCustomerProfile->address2 = $request->input("address2");
            }
            $newCustomerProfile->city = $request->input("city");
            $newCustomerProfile->zipcode = $request->input("zipcode");

            $fileNameProfile = time().'.'.$request->file('profile_image')->getClientOriginalExtension();
            $fileNameState = time().'.'.$request->file('state_id_image')->getClientOriginalExtension();

            $profile_images = $request->file("profile_image");
            $profile_images->storeAs('web_profile_images', $fileNameProfile, 's3');
            $newCustomerProfile->profile_image = 'web_profile_images/' . $fileNameProfile;

            $profile_images = $request->file("state_id_image");
            $profile_images->storeAs('web_state_id_images', $fileNameState, 's3');
            $newCustomerProfile->state_id_image = 'web_state_id_images/' . $fileNameState;

            $newCustomerProfile->save();

            UserFunctions::verifyRequest($request,2);
            return ResponseFormatter::successResponse("User added!");
        }
    }

    public function getUser($id): \Illuminate\Http\JsonResponse
    {
        $user = User::where("id", $id)->first();
        if (!$user)
            return ResponseFormatter::errorResponse('User not found!');
        else {
            return ResponseFormatter::successResponse("User detail found.", $user);
        }
    }

    public function getRoles(): \Illuminate\Http\JsonResponse
    {
        $roles = Roles::all();
        return ResponseFormatter::successResponse("Role detail.", $roles);
    }

    public function getAllUsers(): \Illuminate\Http\JsonResponse
    {
        $users = User::where("user_role_id",Constants::MOBILE_USER)->get();
        $userDetails = UserFunctions::getUser($users);
        return ResponseFormatter::successResponse("Users", $userDetails);
    }

    public function getAllCustomers(): \Illuminate\Http\JsonResponse
    {
        $users = User::where("user_role_id",Constants::WEB_USER)->get();
        $userDetails = UserFunctions::getUser($users);
        return ResponseFormatter::successResponse("Users", $userDetails);
    }

    public function getUserDetails($user_id): \Illuminate\Http\JsonResponse
    {
        $userProfile = UserProfile::where("user_id", $user_id)->first();
        if ($userProfile) {
            $contentData = UserFunctions::getProfileDetailsUser($user_id,$userProfile);
            return ResponseFormatter::successResponse("User Profile", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Profile not found");
        }
    }

    public function getCustomerDetails($user_id): \Illuminate\Http\JsonResponse
    {
        $userProfile = CustomerProfile::where("user_id", $user_id)->first();

        if ($userProfile) {
            $contentData = UserFunctions::getProfileDetailsCustomer($userProfile);
            return ResponseFormatter::successResponse("Web Profile", $contentData);
        } else {
            return ResponseFormatter::errorResponse("Profile not found");
        }
    }

    public function getCurrentUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::where("email", $request->input(Constants::CURRENT_EMAIL_KEY))->first();
        return ResponseFormatter::successResponse("Role detail found.", array('role_name' => $user->role->name));
    }

    public function getCurrentUserInfo(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::where("email", $request->input(Constants::CURRENT_EMAIL_KEY))->first();
        return ResponseFormatter::successResponse("Current user detail found.", $user);
    }

    public function deleteUser(Request $request) {
        $user = User::where("id", $request->input(Constants::CURRENT_ROLE_ID_KEY))->first();
        DB::beginTransaction();

        $user->email = Constants::TEST_EMAIL;
        $user->first_name = Constants::TEST_FIRST_NAME;
        $user->last_name = Constants::TEST_LAST_NAME;
        $user->phone_no = Constants::TEST_PHONE;
        $user->status = Constants::DELETED;
        $user->update();

    }
    public function deactivateUser(Request $request): \Illuminate\Http\JsonResponse
    {

        return ResponseFormatter::successResponse("User deactivated");
    }
}

