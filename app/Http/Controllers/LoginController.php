<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\JwtHelper;
use App\Common\FunctionHelpers\UserFunctions;
use App\Common\ResponseFormatter;
use App\Common\UUID;
use App\Constants;
use App\Models\DeviceTokens;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class LoginController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
            "device_uuid" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors()->first());

        $user = User::where("email", $request->input("email"))
            ->where("active", 1)
            ->where("user_role_id", 3)
            ->first();
        if ($user) {
            if (Hash::check($request->input("password"), $user->password)) {
                list($token, $refreshToken) = UserFunctions::generateToken($user);

                $this->deviceToken($user, $request, $token);

                return ResponseFormatter::successResponse("Login successful.",
                    array("access_token" => (string)$token, "refresh_token" => (string)$refreshToken));
            } else {
                return ResponseFormatter::errorResponse('The password entered is incorrect');
            }
        }
        return ResponseFormatter::errorResponse('The email address entered does not exist or not active');
    }

    public function loginWeb(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("email", $request->input("email"))
            ->where("active", 1)
            ->where("user_role_id", 2)
            ->first();

        if ($user) {
            if (Hash::check($request->input("password"), $user->password)) {
                list($token, $refreshToken) = UserFunctions::generateToken($user);

                return ResponseFormatter::successResponse("Login successful.",
                    array("token" => (string)$token, "refresh_token" => (string)$refreshToken));
            } else {
                return ResponseFormatter::errorResponse( 'The password entered is incorrect');
            }
        }
        return ResponseFormatter::errorResponse( 'The email address entered is incorrect or not active');
    }

    public function loginAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("email", $request->input("email"))
            ->where("active", 1)
            ->where("user_role_id", 1)
            ->first();

        if ($user) {
            if (Hash::check($request->input("password"), $user->password)) {
                list($token, $refreshToken) = UserFunctions::generateToken($user);

                return ResponseFormatter::successResponse("Login successful.",
                    array("token" => (string)$token, "refresh_token" => (string)$refreshToken));
            } else {
                return ResponseFormatter::errorResponse( 'The password entered is incorrect');
            }
        }
        return ResponseFormatter::errorResponse( 'The email address entered is incorrect or not active');
    }

    public function refreshToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $uuid = $request->input(Constants::REFRESH_TOKEN_UUID_KEY);
        $rt = RefreshToken::where("uuid", $uuid)->first();
        $user = User::where("id", $rt->user_id)->first();
        $token = JwtHelper::generateAccessToken($user);
        return response()->json(["success" => true, "status" => "ok", "token" => (string)$token]);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "device_token" => "required",
        ]);
        if ($validator->fails())
            return ResponseFormatter::errorResponse( $validator->errors());

        $deviceToken = DeviceTokens::where('device_token',$request->input("device_token"));
        if ($deviceToken) {
            $deviceToken->delete();
            return ResponseFormatter::successResponse("User Logged out");
        }
        else {
            return ResponseFormatter::errorResponse( "Cannot log out");
        }
    }

    private function deviceToken($user, $request, $token) {
        $newDeviceToken = new DeviceTokens();
        $newDeviceToken->user_id = $user->id;
        $newDeviceToken->device_uuid = $request->input("device_uuid");
        $newDeviceToken->device_type = $request->input("device_type");
        $newDeviceToken->token = $token;
        if ($request->has('device_token')) {
            $newDeviceToken->device_token = $request->input("device_token");
        }
        $newDeviceToken->save();
    }
}
