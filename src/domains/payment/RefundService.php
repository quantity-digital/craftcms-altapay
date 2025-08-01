<?php

namespace QD\altapay\domains\payment;

use craft\commerce\models\Transaction;
use Exception;
use QD\altapay\api\PaymentApi;
use QD\altapay\config\Utils;
use QD\altapay\domains\payment\PaymentResponse;

class RefundService
{
  //* Authorize
  public static function execute(Transaction $transaction): PaymentResponse
  {
    if (!$transaction->reference) throw new Exception('Transaction reference not found');

    $order = $transaction->getOrder();
    if (!$order) throw new Exception('Order not found');

    $gateway = $order->getGateway();
    if (!$gateway) throw new Exception('Gateway not found');
    if (!$gateway->terminal) throw new Exception('Terminal not found');

    $amount = $transaction->amount;
    if (!$amount) throw new Exception('Refund amount not specified');

    $payload = [
      'terminal' => $gateway->terminal,
      'shop_orderid' => $order->reference,
      'amount' => Utils::amount($amount),
      'currency' => $order->paymentCurrency,
      'transaction_id' => $transaction->reference,
    ];

    $response = PaymentApi::refundCapturedReservation($payload);

    return new PaymentResponse($response, $transaction->reference);
  }
}
