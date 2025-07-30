<?php

namespace QD\altapay\services;

use craft\commerce\elements\Order;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use Exception;
use craft\commerce\records\Transaction as RecordsTransaction;

class TransactionService
{
  public static function authorize(string $status, object $response, string $msg = '', string $code = '')
  {
    $parent = self::getTransactionByHash($response->transaction_info->transaction);
    if (!$parent) throw new Exception("Parent transaction not found", 1);

    $order = Order::findOne($response->transaction_info->order);
    if (!$order) throw new Exception("Order not found", 1);

    $transaction = Commerce::getInstance()->getTransactions()->createTransaction($order, $parent);
    $transaction->type = RecordsTransaction::TYPE_AUTHORIZE;
    $transaction->status = $status;
    $transaction->reference = $response->payment_id;
    $transaction->response = $response;
    $transaction->message = $msg;
    $transaction->code = $code;

    $save = Commerce::getInstance()->getTransactions()->saveTransaction($transaction);
    if (!$save) {
      throw new Exception("Transaction could not be saved: " . json_encode($transaction->getErrors()), 1);
    }

    return $transaction;
  }

  public static function capture()
  {
    // TODO: Implement
    return 0;
  }

  public static function refund()
  {
    // TODO: Implement
    return 0;
  }

  //* Utils
  public static function getTransactionById(int $id): ?Transaction
  {
    return Commerce::getInstance()->getTransactions()->getTransactionById($id);
  }

  public static function getTransactionByReference(string $reference): ?Transaction
  {
    return Commerce::getInstance()->getTransactions()->getTransactionByReference($reference);
  }

  public static function getTransactionByHash(string $hash): ?Transaction
  {
    return Commerce::getInstance()->getTransactions()->getTransactionByHash($hash);
  }

  public static function isTransactionSuccessful(Transaction $transaction): bool
  {
    return Commerce::getInstance()->getTransactions()->isTransactionSuccessful($transaction);
  }
}
