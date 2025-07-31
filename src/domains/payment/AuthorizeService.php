<?php

namespace QD\altapay\domains\payment;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Transaction;
use Exception;
use QD\altapay\api\PaymentApi;
use QD\altapay\config\Utils;
use craft\db\Query;
use Throwable;
use craft\commerce\db\Table;

class AuthorizeService
{
  //* Authorize
  public static function execute(Transaction $transaction): PaymentResponse
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
        'cardholder_name' => 'Marcus Bjerringgaard',
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
      ],

      'fraud_service' => 'none',
      'payment_source' => 'eCommerce',
    ];

    $response = PaymentApi::createPaymentRequest($payload);
    return new PaymentResponse($response);
  }

  //* PRIVATE
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
}
