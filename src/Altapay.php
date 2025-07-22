<?php

namespace QD\altapay;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use QD\altapay\config\Events;
use QD\altapay\config\Routes;
use QD\altapay\domains\settings\Settings;

class Altapay extends Plugin
{
  public static $plugin;
  public string $schemaVersion = "5.0.0";
  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;

  use Routes;
  use Events;

  public function init()
  {
    parent::init();
    Craft::setAlias('@QD/altapay', __DIR__);

    $this->routes();
    $this->events();

    self::$plugin = $this;
  }

  protected function createSettingsModel(): ?Model
  {
    return new Settings();
  }

  protected function settingsHtml(): ?string
  {
    return Craft::$app->getView()->renderTemplate('craftcms-altapay/settings', ['settings' => $this->getSettings()]);
  }
}
