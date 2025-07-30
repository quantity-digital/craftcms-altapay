<?php

namespace QD\altapay\api;

use Craft;
use craft\commerce\db\Table;
use craft\commerce\elements\Order;
use craft\commerce\models\Transaction;
use Exception;
use QD\altapay\config\Response;
use QD\altapay\domains\authorize\AuthorizeResponse;
use craft\db\Query;
use QD\altapay\config\Utils;
use QD\altapay\services\TransactionService;
use Throwable;

class PaymentApi extends Api
{
  // ======= DYNAMIC ======= 
  public function __construct()
  {
    parent::__construct();
  }

  // ======= STATIC ======= 
  public static function createPaymentRequest(Transaction $transaction): Response
  {
    //* Order
    $order = $transaction->getOrder();
    if (!$order) throw new Exception("Order not found", 1);

    $customer = $order->getCustomer();
    if (!$customer) throw new Exception("Customer not found", 1);

    // Order reference
    $reference = self::_reference($order);
    if (!$reference) throw new Exception("Order reference not found", 1);

    //* Gateway
    $gateway = $transaction->getGateway();
    if (!$gateway) throw new Exception("Gateway not found", 1);

    // Gateway Terminal
    if (!$gateway->terminal) throw new Exception("Gateway terminal not set", 1);


    $url = Craft::$app->getSites()->getSiteById($order->siteId)->getBaseUrl();
    if (!$url) throw new Exception("Site not found", 1);



    //TODO: Extend this with more data
    $payload = [
      'terminal' => $gateway->terminal,
      'shop_orderid' => $order->reference,
      'amount' => Utils::amount($transaction->paymentAmount),
      'currency' => $order->paymentCurrency,

      'transaction_info' => [
        'store' => $order->storeId ?? '',
        'order' => $order->id ?? '',
        'transaction' => $transaction->hash ?? '',
      ],

      'customer_info' => [
        'username' => $customer->id ?: '',
        'email' => $customer->email ?: '',
        'billing_firstname' => $customer->firstName ?: '',
        'billing_lastname' => $customer->lastName ?: '',
      ],

      'config' => [
        'callback_ok' => $url . 'callback/v1/altapay/payment/ok',
        'callback_fail' => $url . 'callback/v1/altapay/payment/fail',
        'callback_open' => $url . 'callback/v1/altapay/payment/open',
        'callback_notification' => $url . 'callback/v1/altapay/payment/notification',
      ]
    ];

    $response = (new Api())
      ->setMethod('createPaymentRequest')
      ->setPayload($payload)
      ->post();


    // TODO: Handle error state

    return $response;
  }

  private static function _reference(Order &$order): Order
  {
    if ($order->reference) return $order;


    $referenceTemplate = $order->getStore()->getOrderReferenceFormat();
    try {
      $baseReference = Craft::$app->getView()->renderObjectTemplate($referenceTemplate, $order);

      $suffix = 0;
      $testReference = $baseReference;

      while (true) {
        $existingReference = (new Query())
          ->select('id')
          ->from([Table::ORDERS])
          ->where(['reference' => $testReference])
          ->exists();

        if (!$existingReference) {
          $order->reference = $testReference;
          break;
        }

        $suffix++;
        $testReference = $baseReference . '-' . $suffix;
      }

      Craft::$app->getElements()->saveElement($order, false);
    } catch (Throwable $exception) {
      throw $exception;
    }

    if (!$order->reference) {
      throw new Exception('Failed to generate order reference', 1);
    }

    return $order;
  }

  public static function captureReservation(Transaction $transaction, string $reference): Response
  {
    $transaction = TransactionService::getTransactionByReference($reference);
    if (!$transaction) throw new Exception("Authorized transaction not found", 1);

    $order = $transaction->getOrder();
    if (!$order) throw new Exception("Order not found for transaction", 1);

    $authorized = (float) $transaction->paymentAmount;
    $amount = (float) $order->getOutstandingBalance();

    if ($authorized <= 0) throw new Exception("Cannot capture a transaction with an amount of 0 or less", 1);

    // If the amount is larger that the authorized, cap the amount at authorized value
    if ($authorized <= $amount) {
      $amount = $authorized;
    }

    Utils::amount($amount);

    $payload = [
      'amount' => Utils::amount($amount),
      // 'orderLines' = [],
      // 'reconciliation_identifier' => '',
      // 'sales_tax' => 0,
      'transaction_id' => $reference
    ];

    $response = (new Api())
      ->setMethod('captureReservation')
      ->setPayload($payload)
      ->post();

    // TODO: Handle error state

    return $response;
  }


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

    // TODO: Handle error state

    return $response->data->Terminals->Terminal ?? [];
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
