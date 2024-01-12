<?php

namespace App\Common\FunctionHelpers;

use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeHelper
{
    public static function createCustomer($email): \Stripe\Customer
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->customers->create([
            'email' => $email,
        ]);
    }

    public static function addPayment($type, $number, $exp_month, $exp_year, $cvc): \Stripe\PaymentMethod
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentMethods->create([
            'type' => $type,
            'card[number]' => $number,
            'card[exp_month]' => $exp_month,
            'card[exp_year]' => $exp_year,
            'card[cvc]' => $cvc,
        ]);

    }

    public static function attachPayment($customer_id, $payment_method): \Stripe\PaymentMethod
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentMethods->attach($payment_method, [
            'customer' => $customer_id
        ]);
    }

    public static function getPaymentMethodList($customer_id): \Stripe\Collection
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->customers->allPaymentMethods(
            $customer_id,
            ['type' => 'card']
        );
    }

    public static function detachCardFromCustomer($card_id): \Stripe\PaymentMethod
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentMethods->detach(
            $card_id,
            []
        );
    }

    public static function createConnectAccount($email): \Stripe\Account
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->accounts->create([
            'type' => 'express',
            'country' => 'US',
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
    }

    public static function accountSetupLink($account, $return_url, $reauth_url): \Stripe\AccountLink
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->accountLinks->create([
            'account' => $account,
            'refresh_url' => $reauth_url,
            'return_url' => $return_url,
            'type' => 'account_onboarding',
        ]);
    }

    public static function deleteAccount($account): \Stripe\Account
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->accounts->delete(
            $account,
            []
        );
    }

    public static function paymentIntentConnect($customer, $account): \Stripe\PaymentIntent
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentIntents->create([
            'amount' => 10000,
            'currency' => 'usd',
            'customer' => $customer,
            'automatic_payment_methods' => [
                'enabled' => 'true',
            ],
        ], ['stripe_account' => $account]);

    }

    public static function paymentIntentApplication($customer, $amount, $currency): \Stripe\PaymentIntent
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentIntents->create([
            'amount' => $amount * 100,
            'currency' => $currency,
            'customer' => $customer,
            'automatic_payment_methods' => [
                "enabled" => true,
            ],
            'description' => "Gym pass",
        ], [
            'stripe_version' => '2022-08-01',
            'stripe_account' => "acct_1N1lSbGctfbUEiCW"
        ]);
    }

    public static function getPaymentIntent($payment_intentId): \Stripe\PaymentIntent
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentIntents->retrieve($payment_intentId);
    }

    public static function paymentEphemeralKey($customer): \Stripe\EphemeralKey
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->ephemeralKeys->create([
            'customer' => $customer,
        ], [
                'stripe_version' => '2022-08-01',
                'stripe_account' => Config::get('constants.stripe_account')
            ]
        );
    }

    public static function paymentCharge($amount, $customer, $payment_method): \Stripe\PaymentIntent
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentIntents->create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'customer' => $customer,
//            'automatic_payment_methods' => [
//                'enabled' => true,
//            ],

            'payment_method_types' => [
                'card'
            ],
            'payment_method' => $payment_method,
            'capture_method' => "automatic",
            'description' => "None",
            "confirm" => "true",
        ]);
    }

    public static function paymentTransfer($amount, $account): \Stripe\Transfer
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->transfers->create([
            'amount' => $amount,
            'currency' => 'usd',
            'destination' => $account,
        ]);
    }

    public static function retrievePaymentMethods($customer): \Stripe\Collection
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentMethods->all([
            'customer' => $customer,
            'type' => 'card',
        ]);
    }

    public static function cloningPaymentMethods($customer, $payment, $account): \Stripe\PaymentMethod
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentMethods->create([
            'customer' => $customer,
            'payment_method' => $payment,
        ], [
            'stripe_account' => $account,
        ]);
    }

    public static function creatingCharges($token, $account): \Stripe\Customer
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->customers->create(
            ['payment_method' => $token],
            ['stripe_account' => $account]
        );
    }

    public static function creatingChargeForPayment($payment, $customer, $account, $app_fee, $gym_charge): \Stripe\PaymentIntent
    {
        $stripe = new StripeClient(Config::get('constants.sk_test'));
        return $stripe->paymentIntents->create(
            [
                'amount' => $gym_charge * 100,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'customer' => $customer,
                'payment_method' => $payment,
                'capture_method' => "automatic",
                'description' => "None Hello",
                'confirm' => true,
                'application_fee_amount' => $app_fee * 100,
            ],
            ['stripe_account' => $account
            ],
        );
    }

    public static function createSetupIntent($customer_id): \Stripe\SetupIntent
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->setupIntents->create([
                'customer' => $customer_id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]
        );
    }

//    Price
    public static function createPrice($amount, $product_name): \Stripe\Price
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->prices->create([
                'currency' => 'usd',
                'unit_amount' => $amount * 100,
                'product_data' => [
                    'name' => $product_name
                ],
            ]
        );
    }

    public static function updatePrice($price_id, $amount): \Stripe\Price
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->prices->update($price_id, [
            'unit_amount' => $amount,
        ]);
    }

//    Invoice Items
    public static function createInvoiceItem($customer_id, $price_id): \Stripe\InvoiceItem
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->invoiceItems->create([
                'customer' => $customer_id,
                'price' => $price_id
            ]
        );
    }

    public static function deleteInvoiceItem($invoice_item_id): \Stripe\InvoiceItem
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->invoiceItems->delete($invoice_item_id);
    }

// Invoices
    public static function createInvoices($customer_id): \Stripe\Invoice
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->invoices->create([
                'customer' => $customer_id,
                'collection_method' => 'charge_automatically',
            ]
        );
    }

    public static function payInvoices($invoice_id): \Stripe\Invoice
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->invoices->pay($invoice_id);
    }

    public static function voidInvoices($invoice_id): \Stripe\Invoice
    {
        $stripe = new StripeClient(Config::get('constants.mx_test'));
        return $stripe->invoices->voidInvoice($invoice_id);
    }
}
