<?php

namespace QD\altapay\domains\refund;

use craft\commerce\models\Transaction;
use QD\altapay\api\PaymentApi;

class RefundService
{
  //* Authorize
  public static function execute(Transaction $transaction)
  {
    echo '<pre>';
    print_r($transaction);
    echo '</pre>';
    die;



    // TODO: Move formatting of params to this file.
    // $response = PaymentApi::createPaymentRequest($transaction);
    // return new AuthorizeResponse($response);
  }
}
