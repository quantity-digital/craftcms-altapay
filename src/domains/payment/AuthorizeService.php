<?php

namespace QD\altapay\domains\payment;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Transaction;
use Exception;
use QD\altapay\api\PaymentApi;
use QD\altapay\config\Utils;
use craft\db\Query;
use Throwable;
use craft\commerce\db\Table;
use QD\altapay\config\Data;

class AuthorizeService
{
  //* Authorize
  public static function execute(Transaction $transaction): PaymentResponse
  {
    //* Order
    $order = $transaction->getOrder();
    if (!$order) throw new Exception("Order not found", 1);

    //* Reference
    $reference = self::_reference($order);
    if (!$reference) throw new Exception("Order reference not found", 1);

    //* Gateway
    $gateway = $transaction->getGateway();
    if (!$gateway) throw new Exception("Gateway not found", 1);
    if (!$gateway->terminal) throw new Exception("Gateway terminal not set", 1);

    //* Site
    $site = Craft::$app->getSites()->getSiteById($order->siteId);
    if (!$site) throw new Exception("Site not found", 1);

    $payload = [
      // required
      'terminal' => $gateway->terminal,
      'shop_orderid' => $order->reference,
      'amount' => Utils::amount($transaction->paymentAmount),
      'currency' => $order->paymentCurrency,
      'language' => explode('-', $site->language)[0],

      // optional
      'transaction_info' => [
        'store' => $order->storeId ?? '',
        'order' => $order->id ?? '',
        'transaction' => $transaction->hash ?? '',
      ],

      // General
      'type' => Data::PAYMENT_REQUEST_TYPE_PAYMENT,
      // 'sale_reconciliation_identifier' => '',
      // 'credit_card_token' => '',
      'fraud_service' => 'none',
      // 'cookie' => '',
      'payment_source' => 'eCommerce',
      // 'shipping_method' => '',
      // 'customer_created_date' => 'yyy-mm-dd',
      // 'organisation_number' => '',
      // 'account_offer' => '',
      // 'sales_tax' => 0,
    ];

    $payload['customer_info'] = self::_customer($order);
    $payload['config'] = self::_config($site->baseUrl);
    $payload['orderLines'] = self::_lines($order);

    $response = PaymentApi::createPaymentRequest($payload);
    return new PaymentResponse($response);
  }

  //* Payload
  private static function _customer(Order $order): array
  {
    $customer = $order->getCustomer();
    $shipping = $order->getShippingAddress();
    $billing = $order->getBillingAddress();

    $info = [];
    if ($customer) {
      $info += [
        'username' => $customer->id ?: '',
        'email' => $customer->email ?: '',
        'cardholder_name' => $customer->fullName ?? $billing->fullName ?? $shipping->fullName ?? '',
        // 'birthdate' => '',
        // 'gender' => '',
        // 'customer_phone' => '',

        // BANK
        // 'bank_phone' => '',
        // 'bank_name' => ''

        // CLIENT
        // 'client_session_id' => '',
        // 'client_accept_language' => '',
        // 'client_user_agent' => '',
        // 'client_forwarded_ip' => '',
      ];
    }

    if ($shipping) {
      $info += [
        'shipping_lastname' => $shipping->lastName ?? '',
        'shipping_firstname' => $shipping->firstName ?? '',
        'shipping_address' => $shipping->addressLine1 ?? '',
        'shipping_postal' => $shipping->postalCode ?? '',
        // 'shipping_region' => '',
        'shipping_country' => $shipping->countryCode ?? '',
        'shipping_city' => $shipping->locality ?? '',
      ];
    }

    if ($billing) {
      $info += [
        'billing_lastname' => $billing->lastName ?? '',
        'billing_firstname' => $billing->firstName ?? '',
        'billing_address' => $billing->addressLine1 ?? '',
        'billing_postal' => $billing->postalCode ?? '',
        // 'billing_region' => '',
        'billing_country' => $billing->countryCode ?? '',
        'billing_city' => $billing->locality ?? '',
      ];
    }

    return $info;
  }

  private static function _lines(Order $order): array
  {
    $lines = [];

    // Items
    $items = $order->getLineItems();
    if (!$items) return $lines;

    foreach ($items as $item) {
      $purchasable = $item->getPurchasable();
      if (!$purchasable) continue;

      //TODO: Allow setting the field in altapay settings
      $image = $purchasable->image ?? $purchasable->images ?? $purchasable->variantImages ?? null;

      $unitPrice = $item->taxIncluded ? $item->price - $item->taxIncluded : $item->price;
      $taxAmount = $item->taxIncluded ? $item->taxIncluded : 0.00;

      $lines[] = [
        'description' => $item->description ?: '',
        'itemId' => $purchasable->sku ?: '',
        'quantity' => $item->qty ?: 1,

        'unitPrice' => Utils::amount($unitPrice),
        'taxAmount' => Utils::amount($taxAmount),
        'discount' => Utils::amount(self::_discount($item->subtotal, $item->total)),
        'goodsType' => 'item', // TODO: Handle giftvouchers?
        'imageUrl' => $image ? Craft::$app->getAssets()->getAssetUrl($image->eagerly()->one()) : '',
        'productUrl' => $purchasable->url ?: '',
      ];
    }

    // Adjustments
    $adjustments = $order->getAdjustments();
    foreach ($adjustments as $adjustment) {
      if ($adjustment->lineItemId) continue;
      if ($adjustment->type === 'shipping') continue;
      if ($adjustment->type === 'tax') continue;

      switch ($adjustment->type) {
        case 'discount':
          $type = 'discount';
          break;
        default:
          $type = 'item';
          break;
      }

      $lines[] = [
        'description' => $adjustment->name ?: 'Adjustment',
        'itemId' => strtolower(str_replace(' ', '-', $adjustment->name ?: 'adjustment')),
        'quantity' => 1,

        'unitPrice' => Utils::amount($adjustment->amount),
        'taxAmount' => Utils::amount(0.00),
        'discount' => 0.00,
        'goodsType' => $type,
        'imageUrl' => '',
        'productUrl' => '',
      ];
    }

    // Shipping
    $shipping = $order->totalShippingCost ?: null;
    if ($shipping) {
      $lines[] = [
        'description' => $order->shippingMethodName ?: 'Shipping',
        'itemId' => $order->shippingMethodHandle ?: 'shipping',
        'quantity' => 1,

        'unitPrice' => Utils::amount($shipping),
        'taxAmount' => Utils::amount(0.00),
        'discount' => 0.00,
        'goodsType' => 'shipping',
        'imageUrl' => '',
        'productUrl' => '',
      ];
    }

    return $lines;
  }

  private static function _config($url): array
  {
    return [
      'callback_ok' => $url . 'callback/v1/altapay/payment/ok',
      'callback_fail' => $url . 'callback/v1/altapay/payment/fail',
      'callback_open' => $url . 'callback/v1/altapay/payment/open',
      'callback_notification' => $url . 'callback/v1/altapay/payment/notification',
    ];
  }

  //* PRIVATE
  private static function _reference(Order &$order): Order
  {
    if ($order->reference) return $order;


    $referenceTemplate = $order->getStore()->getOrderReferenceFormat();
    try {
      $baseReference = Craft::$app->getView()->renderObjectTemplate($referenceTemplate, $order);

      $suffix = 0;
      $testReference = $baseReference;

      while (true) {
        $existingReference = (new Query())
          ->select('id')
          ->from([Table::ORDERS])
          ->where(['reference' => $testReference])
          ->exists();

        if (!$existingReference) {
          $order->reference = $testReference;
          break;
        }

        $suffix++;
        $testReference = $baseReference . '-' . $suffix;
      }

      Craft::$app->getElements()->saveElement($order, false);
    } catch (Throwable $exception) {
      throw $exception;
    }

    if (!$order->reference) {
      throw new Exception('Failed to generate order reference', 1);
    }

    return $order;
  }

  private static function _discount($subtotal, $total): float
  {
    if ($subtotal <= 0) return 0;
    if ($subtotal === $total) return 0;
    if ($total <= 0) return 100;

    $discount = (($subtotal - $total) / $subtotal) * 100;
    return (float) $discount ?? 0.00;
  }
}
