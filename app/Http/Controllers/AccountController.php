<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\StripeHelper;
use App\Common\ResponseFormatter;
use App\Common\StringTemplate;
use App\Constants;
use App\Jobs\JobInformation;
use App\Models\CustomerProfile;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserProfile;
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

    public function updateAccountStatus(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "accepted" => "required",
        ]);

        if ($validator->fails())
            return ResponseFormatter::errorResponse($validator->errors());

        $user = User::where("id", $id)->first();
        $user_profile = UserProfile::where("user_id", $id)->first();
        if (!$user) {
            return ResponseFormatter::errorResponse('No such User');
        } else {
            if ($request->input("accepted") == 0) {
                $user->active = 1;
                $user->status = 1;
                $user->profile = 0;
                $user_profile->terms_and_condition = 0;
                $user->update();
                $user_profile->update();
                JobInformation::dispatch(
                    $user->email,
                    StringTemplate::typeMessage(Constants::DENIED_ACCOUNT, $request->input("reason"), null, null),
                );
            }
            if ($request->input("accepted") == 1) {
                if ($user->user_role_id == 2) {
                    try {
                        DB::beginTransaction();
                        $customer_profile = CustomerProfile::where("user_id", $id)->first();
                        $user->active = 1;
                        $user->status = 1;
                        $user->profile = 1;
                        $user->update();
                        try {
                            $customer = StripeHelper::createCustomer($user->email);
                        } catch (\Exception $e) {
                            return ResponseFormatter::errorResponse($e->getMessage());
                        }

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
                    $user->active = 1;
                    $user->status = 1;
                    $user->profile = 1;
                    $user->update();
//                return ResponseFormatter::successResponse("User status updated");
                }
                JobInformation::dispatch(
                    $user->email,
                    StringTemplate::typeMessage(Constants::APPROVED_ACCOUNT, null, null, null),
                );
            }
            return ResponseFormatter::successResponse("User status updated");
        }
    }
}
