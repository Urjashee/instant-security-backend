<?php

namespace App\Http\Controllers;

use App\Jobs\ForgotPasswordMail;
use App\Jobs\SendMail;
use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use App\Common\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function resetPasswordRequest(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        $siteName = Config::get('constants.url');
        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("email", $request->input("email"))
            ->where("active", 1)
            ->first();
        if (!$user) {
            return ResponseFormatter::errorResponse( 'The email address entered is incorrect');
        } else {
            $password = PasswordReset::where("email", $request->input("email"))
                ->where('created_at', '>', Carbon::now()->subMinutes(2))
                ->where('type',2)
                ->where('active_token',0)->first();
            if ($password) {
                return ResponseFormatter::errorResponse( 'Password reset link already sent. You can send a new reset password link only after 2 minutes');
                //return response()->json(["success" => false, "status" => "error", "message" => "Password reset link already sent. You can send a new reset password link only after 5 minutes"]);
            } else {
                $token = Str::random(64);
                $newPassword = new PasswordReset();
                $newPassword->email = $request->input("email");
                $newPassword->token = $token;
                $newPassword->active_token = 0;
                $newPassword->type = 2;
                $newPassword->created_at = Carbon::now();
                $newPassword->save();
                ForgotPasswordMail::dispatch($request->input("email"),$token,$user->first_name,$siteName,$user->user_role_id);
//                Mail::to($request->input("email"))
//                    ->send(new ResetPassword($token, $user->first_name, $siteName,2));
                return ResponseFormatter::successResponse("Email sent");
            }
        }
    }

    public function updatePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|alpha_num',
            'token' => 'required',
        ]);
        $token = $request->input("token");
        if ($validator->fails())
            return ResponseFormatter::errorResponse( $validator->errors());
           // return response()->json(["success" => false, "status" => "error", "message" => "Inputs missing"]);

        $password = PasswordReset::where("token", $token)
            ->where('created_at', '>', Carbon::now()->subHours(2))
            ->where('type',2)
            ->where('active_token',0)->first();
        if (!$password) {
            return ResponseFormatter::errorResponse( 'Password reset link has expired');
            //return response()->json(["success" => false, "status" => "error", "message" => "Password reset link has expired"]);
        } else {
            $jsonPayload = [];
            $jsonPayload["email"] = $password->email;
            $user = User::where("email", $jsonPayload["email"])->first();
            if (Hash::check($request->input("password"), $user->password)) {
                return ResponseFormatter::errorResponse( 'Password is the same as old one');
                //return response()->json(["success" => false, "status" => "error", "message" => "Password is the same as old one"]);
            } else {
                if ($password->active_token == 0) {
                    $user->password = Hash::make($request->input("password"));
                    $user->save();
                    $password->active_token = 1;
                    PasswordReset::where('type',2)
                        ->where("email", $jsonPayload["email"])
                        ->where('active_token',0)->update(['active_token' => 1]);
                    $password->update();
                    return ResponseFormatter::successResponse("Password update done!");
                    //return response()->json(["success" => true, "status" => "ok", "message" => "Password update done!"]);
                } else {
                    return ResponseFormatter::errorResponse( 'Password already updated for this link');
                    //return response()->json(["success" => false, "status" => "ok", "error" => "Password already updated for this link"]);
                }
            }
        }
    }

    public function passwordExpiry(Request $request, $token): \Illuminate\Http\JsonResponse
    {
        $password = PasswordReset::where("token", $token)
            ->where('created_at', '>', Carbon::now()->subMinutes(30))
            ->first();
        if (!$password) {
            return response()->json(["success" => false, "status" => "error", "message" => "Password reset link has expired"]);
        }
        if ($password->active_token == 0) {
            return response()->json(["success" => true, "status" => "ok", "message" => "Password update done!"]);
        } else {
            return response()->json(["success" => false, "status" => "ok", "message" => "Password already updated for this link"]);
        }
    }
}
