<?php

namespace QD\altapay\services;

use Craft;
use QD\altapay\queue\CaptureQueue;
use QD\altapay\queue\NotificationQueue;

class QueueService
{
  public static function capture($id): void
  {
    Craft::$app->getQueue()->push(new CaptureQueue(
      [
        'id' => $id
      ]
    ));
  }

  public static function notification(mixed $response): void
  {
    Craft::$app->getQueue()->push(new NotificationQueue(
      [
        'response' => $response
      ]
    ));
  }
}
