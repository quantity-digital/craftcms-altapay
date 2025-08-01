<?php

namespace QD\altapay\domains\payment;

use Craft;
use craft\commerce\base\RequestResponseInterface;

class PaymentResponse implements RequestResponseInterface
{
  protected mixed $response = [];
  public string $reference = '';

  public bool $success = false;

  private string $_redirect = '';
  private bool $_processing = false;

  public function __construct(mixed $response, string $reference = '')
  {
    $this->response = $response;
    $this->reference = $reference ?? '';
    $this->success = $response->success ?? false;

    $isRedirect = isset($response->data->Url) && isset($response->data->PaymentRequestId) && $response->data->Url && !$this->reference;
    if ($isRedirect) $this->setRedirect($response->data->Url);
  }

  public function getTransactionReference(): string
  {
    return $this->reference;
  }

  public function getCode(): string
  {
    return (string) ($this->response->code ?? 0);
  }

  public function getData(): mixed
  {
    return $this->response->data ?? [];
  }

  public function getMessage(): string
  {
    return $this->response->message ?? '';
  }

  //* Success
  public function isSuccessful(): bool
  {
    if ($this->isRedirect()) {
      return false;
    }

    if (!$this->success) {
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
    return !$this->success;
  }
}
