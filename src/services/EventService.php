<?php

namespace QD\altapay\services;

use Exception;
use QD\altapay\config\Data;
use QD\altapay\domains\gateways\PaymentGateway;

class EventService
{
  public static function eventOrderStatusChange($event)
  {
    // Order
    $order = $event->order;
    if (!$order) throw new Exception("Order not found", 1);

    // Gateway
    $gateway = $order->getGateway();
    if (!$gateway) throw new Exception("Gateway not found", 1);
    if (!$gateway instanceof PaymentGateway) return;
    if ($gateway->statusToCapture == Data::NULL_STRING) return;

    // History
    $history = $event->orderHistory?->newStatus?->handle;
    if (!$history) throw new Exception("Order status not found", 1);
    if ($history !== $gateway->statusToCapture) return;

    // Capture
    QueueService::capture($order->id);
  }
}
