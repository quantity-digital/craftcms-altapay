<?php

namespace QD\altapay\domains\subscription;

use craft\commerce\elements\Order;

class SubscriptionService
{
  public static function reserveRecurring(string $agreement, Order $order)
  {
    // Implementation for reserving a recurring payment
  }

  public static function captureRecurring(string $reference)
  {
    // Implementation for capturing a recurring payment
  }
}
