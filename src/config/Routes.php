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
      // $event->rules['api/v1/shop/migrate/variants'] = 'quantity-shop/migration/variants';
    });
  }

  private function cpRoutes(): void
  {
    Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
      // $event->rules['erp'] = 'quantity-erp/product/index';
    });
  }
}
