<?php

/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Quantity Digital
 * @license MIT
 */

namespace QD\altapay\domains\gateways;

use Craft;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\elements\Order;
use craft\commerce\models\payments\OffsitePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\Transaction;
use craft\commerce\errors\NotImplementedException;
use craft\helpers\App;
use craft\web\Response as WebResponse;
use yii\base\NotSupportedException;

trait PaymentTrait
{
  public function getPaymentFormHtml(array $params): string
  {
    return '';
  }

  public function createPaymentSource(BasePaymentForm $sourceData, int $userId): PaymentSource
  {
    throw new NotImplementedException('Not implemented by the payment gateway');
  }

  public function deletePaymentSource($token): bool
  {
    return false;
  }

  public function processWebHook(): WebResponse
  {
    throw new NotImplementedException('Not implemented by the payment gateway');
  }

  public function getPaymentFormModel(): BasePaymentForm
  {
    return new OffsitePaymentForm();
  }

  public function completeAuthorize(Transaction $transaction): RequestResponseInterface
  {
    throw new NotSupportedException(Craft::t('commerce', 'Complete Authorize is not supported by this gateway'));
  }

  public function completePurchase(Transaction $transaction): RequestResponseInterface
  {
    throw new NotImplementedException('Not implemented by the payment gateway');
  }

  public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
  {
    throw new NotImplementedException('Not implemented by the payment gateway');
  }

  //* Order condition
  public function availableForUseWithOrder(Order $order): bool
  {
    if ($this->getOnlyAllowForZeroPriceOrders() && $order->getTotalPrice() != 0) {
      return false;
    }

    return parent::availableForUseWithOrder($order);
  }

  public function getOnlyAllowForZeroPriceOrders(bool $parse = true): bool|string
  {
    return $parse ? App::parseBooleanEnv($this->_onlyAllowForZeroPriceOrders) : $this->_onlyAllowForZeroPriceOrders;
  }

  public function setOnlyAllowForZeroPriceOrders(bool|string $onlyAllowForZeroPriceOrders): void
  {
    $this->_onlyAllowForZeroPriceOrders = $onlyAllowForZeroPriceOrders;
  }

  //* Payment types
  public function getPaymentTypeOptions(): array
  {
    return [
      'authorize' => Craft::t('commerce', 'Authorize Only (Manually Capture)'),
    ];
  }

  //* Supports
  public function supportsAuthorize(): bool
  {
    return self::SUPPORTS['Authorize'];
  }

  /**
   * Returns true if gateway supports capture requests.
   *
   * @return bool
   */
  public function supportsCapture(): bool
  {
    return self::SUPPORTS['Capture'];
  }

  /**
   * Returns true if gateway supports completing authorize requests
   *
   * @return bool
   */
  public function supportsCompleteAuthorize(): bool
  {
    return self::SUPPORTS['CompleteAuthorize'];
  }

  /**
   * Returns true if gateway supports completing purchase requests
   *
   * @return bool
   */
  public function supportsCompletePurchase(): bool
  {
    return self::SUPPORTS['CompletePurchase'];
  }

  /**
   * Returns true if gateway supports payment sources
   *
   * @return bool
   */
  public function supportsPaymentSources(): bool
  {
    return self::SUPPORTS['PaymentSources'];
  }

  /**
   * Returns true if gateway supports purchase requests.
   *
   * @return bool
   */
  public function supportsPurchase(): bool
  {
    return self::SUPPORTS['Purchase'];
  }

  /**
   * Returns true if gateway supports refund requests.
   *
   * @return bool
   */
  public function supportsRefund(): bool
  {
    return self::SUPPORTS['Refund'];
  }

  /**
   * Returns true if gateway supports partial refund requests.
   *
   * @return bool
   */
  public function supportsPartialRefund(): bool
  {
    return self::SUPPORTS['PartialRefund'];
  }

  /**
   * Returns true if gateway supports webhooks.
   *
   * @return bool
   */
  public function supportsWebhooks(): bool
  {
    return self::SUPPORTS['Webhooks'];
  }
}
