<?php

namespace QD\altapay\domains\capture;

use QD\altapay\api\PaymentApi;

class CaptureService
{
  //* Authorize
  public static function execute(string $reference)
  {
    $response = PaymentApi::captureReservation($reference);
    return new CaptureResponse($response, $reference);
  }
}
