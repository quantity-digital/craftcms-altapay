<?php

namespace QD\altapay\domains\authorize;

use craft\commerce\models\Transaction;
use QD\altapay\api\PaymentApi;

class AuthorizeService
{
  //* Authorize
  public static function execute(Transaction $transaction): AuthorizeResponse
  {
    // TODO: Move formatting of params to this file.
    $response = PaymentApi::createPaymentRequest($transaction);
    return new AuthorizeResponse($response);
  }
}
