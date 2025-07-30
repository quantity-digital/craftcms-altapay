<?php

namespace QD\altapay\services;

use Craft;
use QD\altapay\queue\CaptureQueue;

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
}
