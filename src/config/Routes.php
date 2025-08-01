<?php

namespace QD\altapay\config;

use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

trait Routes
{
  private function routes(): void
  {
    $this->publicRoutes();
    $this->cpRoutes();
  }

  private function publicRoutes(): void
  {
    Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
      // Api

      // Callback
      $event->rules['callback/v1/altapay/payment/ok'] = 'craftcms-altapay/payment-callback/ok';
      $event->rules['callback/v1/altapay/payment/fail'] = 'craftcms-altapay/payment-callback/fail';
      $event->rules['callback/v1/altapay/payment/open'] = 'craftcms-altapay/payment-callback/open';
      $event->rules['callback/v1/altapay/payment/notification'] = 'craftcms-altapay/payment-callback/notification';
    });
  }

  private function cpRoutes(): void
  {
    Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
      // $event->rules['erp'] = 'quantity-erp/product/index';
    });
  }
}
