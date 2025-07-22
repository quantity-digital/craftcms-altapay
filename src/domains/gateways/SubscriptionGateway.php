<?php

namespace QD\altapay\domains\gateways;

use Craft;
use craft\commerce\base\Gateway;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\elements\Order;
use craft\commerce\errors\NotImplementedException;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\payments\OffsitePaymentForm;
use craft\commerce\models\PaymentSource;
use craft\commerce\models\responses\Manual as ManualRequestResponse;
use craft\commerce\models\Transaction;
use craft\helpers\App;
use craft\web\Response as WebResponse;


class SubscriptionGateway extends Gateway
{
  /**
   * @var bool
   */
  private string|bool $_onlyAllowForZeroPriceOrders = false;

  public function getSettings(): array
  {
    $settings = parent::getSettings();
    $settings['onlyAllowForZeroPriceOrders'] = $this->getOnlyAllowForZeroPriceOrders(false);

    return $settings;
  }

  /**
   * Returns the display name of this class.
   *
   * @return string The display name of this class.
   */
  public static function displayName(): string
  {
    return Craft::t('commerce', 'Altapay Subscription');
  }

  /**
   * @inheritdoc
   */
  public function getPaymentFormHtml(array $params): ?string
  {
    return '';
  }

  /**
   * @inheritdoc
   */
  public function getPaymentFormModel(): BasePaymentForm
  {
    return new OffsitePaymentForm();
  }

  /**
   * @inheritdoc
   */
  public function getSettingsHtml(): ?string
  {
    return Craft::$app->getView()->renderTemplate('commerce/gateways/manualGatewaySettings', ['gateway' => $this]);
  }

  /**
   * @inheritdoc
   */
  public function authorize(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
  {
    return new ManualRequestResponse();
  }

  /**
   * @inheritdoc
   */
  public function capture(Transaction $transaction, string $reference): RequestResponseInterface
  {
    return new ManualRequestResponse();
  }

  /**
   * @inheritdoc
   */
  public function completeAuthorize(Transaction $transaction): RequestResponseInterface
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function completePurchase(Transaction $transaction): RequestResponseInterface
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function createPaymentSource(BasePaymentForm $sourceData, int $customerId): PaymentSource
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function deletePaymentSource(string $token): bool
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function getPaymentTypeOptions(): array
  {
    return [
      'authorize' => Craft::t('commerce', 'Authorize Only (Manually Capture)'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function processWebHook(): WebResponse
  {
    throw new NotImplementedException(Craft::t('commerce', 'This gateway does not support that functionality.'));
  }

  /**
   * @inheritdoc
   */
  public function refund(Transaction $transaction): RequestResponseInterface
  {
    return new ManualRequestResponse();
  }

  /**
   * @inheritdoc
   */
  public function supportsAuthorize(): bool
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function supportsCapture(): bool
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function supportsCompleteAuthorize(): bool
  {
    return false;
  }

  /**
   * @inheritdoc
   */
  public function supportsCompletePurchase(): bool
  {
    return false;
  }

  /**
   * @inheritdoc
   */
  public function supportsPaymentSources(): bool
  {
    return false;
  }

  /**
   * @inheritdoc
   */
  public function supportsPurchase(): bool
  {
    return false;
  }

  /**
   * @inheritdoc
   */
  public function supportsRefund(): bool
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function supportsPartialRefund(): bool
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function supportsWebhooks(): bool
  {
    return false;
  }

  /**
   * @inheritdoc
   */
  public function availableForUseWithOrder(Order $order): bool
  {
    if ($this->getOnlyAllowForZeroPriceOrders() && $order->getTotalPrice() != 0) {
      return false;
    }

    return parent::availableForUseWithOrder($order);
  }

  /**
   * @param bool $parse
   * @return bool|string
   * @since 4.1.1
   */
  public function getOnlyAllowForZeroPriceOrders(bool $parse = true): bool|string
  {
    return $parse ? App::parseBooleanEnv($this->_onlyAllowForZeroPriceOrders) : $this->_onlyAllowForZeroPriceOrders;
  }

  /**
   * @param bool|string $onlyAllowForZeroPriceOrders
   * @return void
   * @since 4.1.1
   */
  public function setOnlyAllowForZeroPriceOrders(bool|string $onlyAllowForZeroPriceOrders): void
  {
    $this->_onlyAllowForZeroPriceOrders = $onlyAllowForZeroPriceOrders;
  }
}
