<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Common\ResponseFormatter;
use App\Jobs\ForgotPasswordMail;
use App\Jobs\SendMail;
use App\Mail\ResetPassword;
use App\Mail\VerifyEmail;
use App\Models\PasswordReset;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function sendEmail() {
//        ForgotPasswordMail::dispatch('urja@simpalm.com','token123','http://localhost:3000',2,2);
        SendMail::dispatch('urja@simpalm.com','token123','http://localhost:3000');
        return ResponseFormatter::successResponse("User added!");
    }

    public function addNewUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "role_id" => "required|numeric|in:1,2",
            "first_name" => "alpha",
            "last_name" => "alpha",
            "password" => "min:8|alpha_num",
        ]);
        $siteName = Config::get('constants.url');
        if ($validator->fails())
            return ResponseFormatter::errorResponse( $validator->errors());
            //return response()->json(["success" => false, "status" => "error", "message" => "Missing required input"]);

        $user = User::where("email", $request->input("email"))->first();
        if ($user) {
            return ResponseFormatter::errorResponse( 'User already exists!');
            //return response()->json(["success" => false, "status" => "error", "message" => "User already exists!"]);
        } else {
            $newUser = new User;
            $newUser->email = $request->input("email");
            $newUser->user_role_id = $request->input("role_id");
            $newUser->active = 0;
            if ($request->has('first_name')) {
                $newUser->first_name = $request->input("first_name");
            }
            if ($request->has('last_name')) {
                $newUser->last_name = $request->input("last_name");
            }
            if ($request->has('dob')) {
                $newUser->dob = $request->input("dob");
            }
            if ($request->has('password')) {
                $newUser->password = Hash::make($request->input("password"));
            }
            $newUser->save();

            $token = Str::random(64);
            $newPassword = new PasswordReset();
            $newPassword->email = $request->input("email");
            $newPassword->token = $token;
            $newPassword->active_token = 0;
            $newPassword->type = 1;
            $newPassword->created_at = Carbon::now();
            $newPassword->save();
            SendMail::dispatch($request->input("email"),$token,$siteName,$request->input("role_id"));
            return ResponseFormatter::successResponse("User added!");
                //return response()->json(["success" => true, "status" => "ok", "message" => "User added!"]);
        }
    }

    public function verifyAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse( $validator->errors());
            //return response()->json(["success" => false, "status" => "error", "message" => "Missing required input"]);

        $token = PasswordReset::where("token", $request->input("token"))->first();
        if (!$token) {
            return ResponseFormatter::errorResponse( 'Incorrect token or type');
           // return response()->json(["success" => false, "status" => "error", "message" => "Incorrect token or type"]);
        } else {
            $email = $token->email;
            $id = $token->id;
            $user = User::where("email", $email)->first();
            if (!$user)
                return ResponseFormatter::errorResponse( 'User not found!');
                //return response()->json(["success" => false, "status" => "error", "message" => "User not found!"]);
            else {
                if ($user) {
                    $user->active = 1;
                    $user->email_verified_at = Carbon::now()->toDateTimeString();
                    $user->update();
                    $tokenData = PasswordReset::where("id", $id)->first();
                    $tokenData->active_token = 1;
                    $tokenData->update();
                    return ResponseFormatter::successResponse("Account verified!");
                    //return response()->json(["success" => true, "status" => "ok", "message" => "Account verified!"]);
                } else {
                    return ResponseFormatter::errorResponse( 'Account not verified!');
                    //return response()->json(["success" => false, "status" => "ok", "message" => "Account not verified!"]);
                }
            }
        }
    }

    public function getUser($id)
    {
        $user = User::where("id", $id)->first();
        if (!$user)
            return ResponseFormatter::errorResponse( 'User not found!');
            //return response()->json(["success" => false, "status" => "error", "message" => "User not found!"]);
        else {
            return ResponseFormatter::successResponse("User detail found.",$user);
            //return response()->json(["success" => true, "status" => "ok", "data" => $user]);
        }
    }

    public function getRoles(): \Illuminate\Http\JsonResponse
    {
        $roles = Roles::all();
        return ResponseFormatter::successResponse("Role detail.",$roles);
        //return response()->json(["success" => true, "status" => "ok", "data" => $roles]);
    }

    public function getAllUsers(): \Illuminate\Http\JsonResponse
    {
        $users = User::all();
        return ResponseFormatter::successResponse(" detail.",$users);
        //return response()->json($users);
    }

    public function getCurrentUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::where("email", $request->input(Constants::CURRENT_EMAIL_KEY))->first();
        return ResponseFormatter::successResponse("Role detail found.",array('role_name'=> $user->role->name));
    }

    public function getCurrentUserInfo(Request $request)
    {
        $user = User::where("email", $request->input(Constants::CURRENT_EMAIL_KEY))->first();
        return ResponseFormatter::successResponse("Current user detail found.",$user);
    }
}

