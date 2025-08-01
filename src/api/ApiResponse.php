<?php

namespace QD\altapay\api;

class ApiResponse
{
  public function __construct(
    public mixed $data = null,
    public mixed $meta = null,

    public int $code = 200,
    public string $message = 'OK',

    public bool $success = true
  ) {}


  public static function success(
    mixed $data = null,
    mixed $meta = null,
    string $msg = 'OK'
  ) {
    return new self($data, $meta, 200, $msg, true);
  }

  public static function error(
    mixed $data = null,
    mixed $meta = null,

    int $code = 500,
    string $msg = 'Error'
  ) {
    return new self($data, $meta, $code, $msg, false);
  }
}
