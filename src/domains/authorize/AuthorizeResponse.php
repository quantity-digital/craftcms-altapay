<?php

namespace QD\altapay\domains\authorize;

use Craft;
use craft\commerce\base\RequestResponseInterface;

class AuthorizeResponse implements RequestResponseInterface
{
  // @var
  protected mixed $response = [];
  public string $message = '';
  public bool $error = false;

  private string $_redirect = '';
  private bool $_processing = false;
  private int $_code = 200;

  public function __construct(mixed $response)
  {
    $this->response = $response;

    if (!$this->response->success) $this->setError(true);
    if ($response->data->Url) $this->setRedirect($response->data->Url);
  }

  public function getTransactionReference(): string
  {
    return '';
  }

  public function getCode(): string
  {
    if ($this->response->error) {
      return (string) 500;
    }

    return (string) $this->_code;
  }

  public function getData(): mixed
  {
    return $this->response->data ?? [];
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  //* Success
  public function isSuccessful(): bool
  {
    if ($this->isRedirect()) {
      return false;
    }

    if ($this->error) {
      return false;
    }

    return true;
  }

  //* Processing
  public function isProcessing(): bool
  {
    return $this->_processing;
  }

  public function setProcessing(bool $bool): void
  {
    $this->_processing = $bool;
  }


  //* Redirect
  public function isRedirect(): bool
  {
    return !empty($this->_redirect);
  }

  public function setRedirect(string $url)
  {
    $this->_redirect = $url;
  }

  public function getRedirectMethod(): string
  {
    return 'GET';
  }

  public function getRedirectData(): array
  {
    return [];
  }

  public function getRedirectUrl(): string
  {
    return $this->_redirect;
  }

  public function redirect(): void
  {
    Craft::$app->getResponse()->redirect($this->_redirect)->send();
  }

  //* Error
  public function isError(): bool
  {
    return $this->error;
  }

  public function setError(bool $error): void
  {
    $this->error = $error;
  }
}
