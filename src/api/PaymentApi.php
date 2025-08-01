<?php

namespace QD\altapay\api;

use QD\altapay\api\ApiResponse;

class PaymentApi extends Api
{
  // ======= DYNAMIC ======= 
  public function __construct()
  {
    parent::__construct();
  }

  // ======= STATIC ======= 
  public static function createPaymentRequest(array $payload): ApiResponse
  {
    $response = (new Api())
      ->setMethod('createPaymentRequest')
      ->setPayload($payload)
      ->post();

    return $response;
  }

  public static function captureReservation(array $payload): ApiResponse
  {
    $response = (new Api())
      ->setMethod('captureReservation')
      ->setPayload($payload)
      ->post();

    return $response;
  }

  //* Refund
  public static function refundCapturedReservation(array $payload): ApiResponse
  {
    $response = (new Api())
      ->setMethod('refundCapturedReservation')
      ->setPayload($payload)
      ->post();

    return $response;
  }

  public static function credit()
  {
    return 'Not implemented';
  }

  //* Reservation
  public static function releaseReservation()
  {
    return 'Not implemented';
  }

  public static function reservation()
  {
    return 'Not implemented';
  }

  //* Invoice
  public static function createInvoiceReservation()
  {
    return 'Not implemented';
  }

  public static function getInvoiceText()
  {
    return 'Not implemented';
  }

  //* Utils
  /**
   * Fetch all shop terminals
   * Terminals are use to identify the payment method
   * ? Altapay uses the "Title" as the identifier when being used in other endpoints
   * TODO: This should return a properly formatted array of terminal models, not raw data
   *
   * @return array
   */
  public static function getTerminals(): array
  {
    $response = (new Api())->setMethod('getTerminals')->get();
    if (!$response->success) return ['Title' => 'API ERROR'];

    return $response->data->Terminals->Terminal ?? ['Title' => 'API ERROR'];
  }

  public static function getCustomReport()
  {
    return 'Not implemented';
  }

  public static function listRefundFiles()
  {
    return 'Not implemented';
  }

  public static function fundingList()
  {
    return 'Not implemented';
  }

  public static function fundingDownload()
  {
    return 'Not implemented';
  }

  public static function calculateSurcharge()
  {
    return 'Not implemented';
  }

  public static function payments()
  {
    return 'Not implemented';
  }

  public static function updateOrder()
  {
    return 'Not implemented';
  }
}
