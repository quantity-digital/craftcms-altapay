<?php

namespace QD\altapay\domains\capture;

use craft\commerce\models\Transaction;
use QD\altapay\api\PaymentApi;

class CaptureService
{
  //* Authorize
  public static function execute(Transaction $transaction, string $reference)
  {
    $response = PaymentApi::captureReservation($transaction, $reference);

    echo '<pre>';
    print_r($response);
    echo '</pre>';
    die;

    return new CaptureResponse($response, $reference);
  }
}
