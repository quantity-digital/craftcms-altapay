<?php

namespace QD\altapay\config;

class Response
{
  public function __construct(
    public mixed $data = null,
    public mixed $meta = null,
    public string $message = 'OK',
    public bool $success = true
  ) {}


  public static function success(
    mixed $data = null,
    mixed $meta = null,
    string $msg = 'OK'
  ) {
    return new self($data, $meta, $msg, true);
  }

  public static function error(
    mixed $data = null,
    mixed $meta = null,
    string $msg = 'Error'
  ) {
    return new self($data, $meta, $msg, false);
  }
}
