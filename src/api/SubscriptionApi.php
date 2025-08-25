<?php

namespace QD\altapay\api;

class SubscriptionApi extends Api
{
  public static function chargeSubscription()
  {
    return 'Not implemented';
  }
  public static function reserveSubscriptionCharge()
  {
    return 'Not implemented';
  }
  public static function createSubscription(array $payload, $type = 'subscription')
  {
    $payload['type'] = $type;
    return PaymentApi::createPaymentRequest($payload);
  }
}
