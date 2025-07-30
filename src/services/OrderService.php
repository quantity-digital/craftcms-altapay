<?php

namespace QD\altapay\services;

use Craft;
use Exception;
use QD\altapay\config\Data;
use craft\commerce\Plugin as Commerce;

class OrderService
{
  public static function setAfterCaptureStatus($order)
  {
    $gateway = $order->getGateway();
    if (!$gateway) throw new Exception("Gateway not found", 1);
    if (!$gateway->statusAfterCapture === Data::NULL_STRING) return;

    $status = Commerce::getInstance()->getOrderStatuses()->getOrderStatusByHandle($gateway->statusAfterCapture, $order->storeId);
    if (!$status) throw new Exception("Order status not found", 1);

    $order->orderStatusId = $status->id;
    Craft::$app->getElements()->saveElement($order);
  }
}
