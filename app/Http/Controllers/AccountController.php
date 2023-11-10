<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\StripeHelper;
use App\Common\ResponseFormatter;
use App\Models\CustomerProfile;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function verifyAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $token = PasswordReset::where("token", $request->input("token"))->first();
        if (!$token) {
            return ResponseFormatter::errorResponse('Incorrect token or type');
        } else {
            $email = $token->email;
            $id = $token->id;
            $user = User::where("email", $email)->first();
            if (!$user)
                return ResponseFormatter::errorResponse('User not found!');
            else {
                if ($user) {
                    $user->active = 1;
                    $user->email_verified_at = Carbon::now()->toDateTimeString();
                    $user->update();
                    $tokenData = PasswordReset::where("id", $id)->first();
                    $tokenData->active_token = 1;
                    $tokenData->update();
                    return ResponseFormatter::successResponse("Account verified!");
                } else {
                    return ResponseFormatter::errorResponse('Account not verified!');
                }
            }
        }
    }

    public function updateAccountStatus($id): \Illuminate\Http\JsonResponse
    {
        $user = User::where("id", $id)->first();
        if (!$user) {
            return ResponseFormatter::errorResponse('No such User');
        } else {
            if ($user->user_role_id == 2) {
                try {
                    DB::beginTransaction();
                    $customer_profile = CustomerProfile::where("user_id", $id)->first();
                    $user->status = 1;
                    $user->update();
                    $customer = StripeHelper::createCustomer($user->email);
                    $customer_profile->customer_id = $customer->id;
                    $customer_profile->update();
                    DB::commit();
//                    return ResponseFormatter::successResponse("User status updated");
                } catch (\Exception $exception) {
                    DB::rollback();
                    return ResponseFormatter::errorResponse($exception->getMessage());
                }
            }
            if ($user->user_role_id == 3) {
                $user->status = 1;
                $user->update();
//                return ResponseFormatter::successResponse("User status updated");
            }
            return ResponseFormatter::successResponse("User status updated");
        }
    }
}
