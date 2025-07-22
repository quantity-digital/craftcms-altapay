<?php

namespace QD\altapay\api;

class PaymentApi extends Api
{
  //* Payment
  public static function createPaymentRequest()
  {
    return 'Not implemented';
  }

  public static function captureReservation()
  {
    return 'Not implemented';
  }

  //* Terminals
  public static function getTerminals()
  {
    return 'Not implemented';
  }

  //* Refund
  //? Refund if captured, otherwise release reservation
  public static function refund()
  {
    return 'Not implemented';
  }

  public static function refundCapturedReservation()
  {
    return 'Not implemented';
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
