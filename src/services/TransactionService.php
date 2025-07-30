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

  public static function capture(string $status, object $response, string $msg = '', string $code = '')
  {
    $parent = self::getTransactionByReference($response->payment_id);
    if (!$parent) throw new Exception("Parent transaction not found", 1);

    $order =  $parent->getOrder();
    if (!$order) throw new Exception("Order not found", 1);

    $transaction = Commerce::getInstance()->getTransactions()->createTransaction($order, $parent);
    $transaction->type = RecordsTransaction::TYPE_CAPTURE;
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

  public static function getSuccessfulTransaction($orderId)
  {
    $transactions = Commerce::getInstance()->getTransactions()->getAllTransactionsByOrderId($orderId);

    $parents = [];
    foreach ($transactions as $transaction) {
      if ($transaction->parentId) {
        $parents[] = $transaction->parentId;
      }
    }

    // Filter for successful authorize transactions that don't have children
    $validTransactions = array_filter($transactions, function ($transaction) use ($parents) {
      return $transaction->type === RecordsTransaction::TYPE_AUTHORIZE &&
        $transaction->status === RecordsTransaction::STATUS_SUCCESS &&
        !in_array($transaction->id, $parents);
    });

    // If no transactions found, return null
    if (empty($validTransactions)) {
      return null;
    }

    // Sort by dateCreated in descending order (newest first)
    usort($validTransactions, function ($a, $b) {
      return $b->dateCreated <=> $a->dateCreated;
    });

    // Return the first (newest) transaction
    return $validTransactions[0];
  }

  public static function captureTransaction(Transaction $transaction)
  {
    $order = $transaction->getOrder();
    if (!$order) throw new Exception("Order not found for transaction", 1);

    $child = Commerce::getInstance()->getPayments()->captureTransaction($transaction);

    switch ($child->status) {
      case RecordsTransaction::STATUS_SUCCESS:
        $order->updateOrderPaidInformation();
        break;
      case RecordsTransaction::STATUS_PROCESSING:
        break;
      case RecordsTransaction::STATUS_PENDING:
        break;
      default:
        throw new Exception('Could not capture payment');
        break;
    }

    return $child;
  }
}
