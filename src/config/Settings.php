<?php

namespace QD\altapay\config;

use craft\base\Model;

class Settings extends Model
{
  // API
  public $username = '';
  public $password = '';
  public $shop = '';

  // Rules
  public function rules(): array
  {
    return [
      [['username', 'password', 'shop'], 'required'],
    ];
  }
}
