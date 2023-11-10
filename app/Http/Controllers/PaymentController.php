<?php

namespace App\Http\Controllers;

use App\Common\FunctionHelpers\StripeHelper;
use App\Common\ResponseFormatter;
use App\Constants;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function getEphemeralKey(Request $request): \Illuminate\Http\JsonResponse
    {
        $customer_profile = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();

        if ($customer_profile)
        {
            $setupIntent = StripeHelper::createSetupIntent($customer_profile->customer_id);
            $paymentEphemeralKey = StripeHelper::paymentEphemeralKey($customer_profile->customer_id);
            $contentsDecoded = [
                "setup_intent" => $setupIntent->id,
                "setup_intent_client_secret" => $setupIntent->client_secret,
                "ephemeral_key" => $paymentEphemeralKey->secret,
                "customer_id" => $customer_profile->customer_id,
                "publishable_key" => Config::get('constants.pk_test'),
            ];
            return ResponseFormatter::successResponse("Ephemeral key", $contentsDecoded);
        } else {
            return ResponseFormatter::errorResponse("Could not create ephemeral key");
        }
    }
    public function getUserCard(Request $request): \Illuminate\Http\JsonResponse
    {
        $customer_profile = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($customer_profile)
        {
            $cardList = StripeHelper::getPaymentMethodList($customer_profile->customer_id);
//            getPaymentMethodList
            return ResponseFormatter::successResponse($cardList);
        } else {
            return ResponseFormatter::errorResponse("No such user founds");
        }
    }

    public function deleteCard(Request $request,$card): \Illuminate\Http\JsonResponse
    {
        $customer_profile = CustomerProfile::where("user_id", $request->input(Constants::CURRENT_USER_ID_KEY))->first();
        if ($customer_profile)
        {
            $cardLists = StripeHelper::getPaymentMethodList($customer_profile->customer_id);
            foreach ($cardLists as $cardList) {
                if ($cardList->id == $card) {
                    StripeHelper::detachCardFromCustomer($cardList->id);
                }
            }
            return ResponseFormatter::successResponse("Card deleted");
        } else {
            return ResponseFormatter::errorResponse("No such user founds");
        }
    }

}
