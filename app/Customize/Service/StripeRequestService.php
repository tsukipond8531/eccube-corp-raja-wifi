<?php

namespace Customize\Service;

use Customize\Entity\CreditCard;
use Eccube\Common\EccubeConfig;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Card;
use Stripe\Stripe;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class StripeRequestService
{
    /**
     * @var eccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var Config
     */
    protected $Config;

    public function __construct(
        EccubeConfig $eccubeConfig
    ) {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * カスタマーを登録する
     *
     * @return String or NULL
     */
    public function createCustomerOnStripeService(\Eccube\Entity\Customer $customer){
        $stripeCustomerId = null;
        Stripe::setApiKey($this->eccubeConfig['stripe_secret_key']);

        if (false === $customer->hasStripeCustomerId()) {
            $stripeCustomer = Customer::create([
                "email" => $customer->getEmail()
            ]);
            logs('stripe')->info($stripeCustomer->status);
            $stripeCustomerId = $stripeCustomer->id;
        }
        return $stripeCustomerId;
    }

    /**
     * Stripeサーバーに決済用クレジットカード情報を登録
     *
     * @retrun String or NULL
     *
     */
    public function createCreditCardOnStripeService(CreditCard $creditCard, \Eccube\Entity\Customer $customer){
        Stripe::setApiKey($this->eccubeConfig['stripe_secret_key']);

        $stripe_cardkey = "";
        try {

        $stripeCard = PaymentMethod::create(
            [
                "type" => "card",
                "card" => [
                    "number" => $creditCard->getCreditCardNumber(),
                    "exp_month" => $creditCard->getExpirationMonth(),
                    "exp_year" => $creditCard->getExpirationYear(),
                    "cvc" => $creditCard->getSecurityCode()
                ]
            ]
        );
        logs('stripe')->info($stripeCard->status);
        $stripe_cardkey = $stripeCard->id;

        $payment_method = PaymentMethod::retrieve($stripe_cardkey);
        $stripeCardAttach = $payment_method->attach(
            [
                "customer" => $customer->getStripeCustomerId()
            ]
        );
        logs('stripe')->info($stripeCardAttach->status);

        } catch(\Stripe\Exception\CardException $e) {
            logs('stripe')->info($e);
        }

        return $stripe_cardkey;
    }



    /**
     * Stripeサーバーに決済用クレジットカード情報を削除
     *
     * @retrun String or NULL
     *
     */
    public function deleteCreditCardOnStripeService(CreditCard $creditCard, \Eccube\Entity\Customer $customer){
        Stripe::setApiKey($this->eccubeConfig['stripe_secret_key']);

        try {

        $payment_method = PaymentMethod::retrieve($creditCard->getStripeCardkey());
        $stripeCardAttach = $payment_method->detach([]);
        logs('stripe')->info($stripeCardAttach->status);
        } catch(\Stripe\Exception\CardException $e) {
            logs('stripe')->info($e);
        }
    }
}
