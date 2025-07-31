<?php

namespace QD\altapay\controllers;

use Craft;
use craft\web\Controller;
use QD\altapay\config\Data;
use QD\altapay\config\Utils;
use QD\altapay\domains\payment\AuthorizeCallbackService;

use Exception;
use QD\altapay\services\QueueService;
use Throwable;

class PaymentCallbackController extends Controller
{
  public $enableCsrfValidation = false;
  protected array|bool|int $allowAnonymous = [
    'ok' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE,
    'fail' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE,
    'open' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE,
    'notification' => self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE,
  ];


  // callback_ok
  //? This callback is called when the order is successfully authorized
  public function actionOk()
  {
    try {
      $response = $this->_response();
      $this->_validate($response);
      return AuthorizeCallbackService::synchronous(Data::CALLBACK_OK, $response);
    } catch (Throwable $th) {
      throw new Exception($th->getMessage(), 1);
    }
  }

  // callback_fail
  //? This callback is called when the autorization failed, and the payment was not completed
  public function actionFail()
  {
    try {
      $response = $this->_response();
      $this->_validate($response);
      return AuthorizeCallbackService::synchronous(Data::CALLBACK_FAIL, $response);
    } catch (Throwable $th) {
      throw new Exception($th->getMessage(), 1);
    }
  }

  // callback_open
  //? This callback is called when the payment is not yet processed (most likely awaiting 3rd party verification)
  public function actionOpen()
  {
    try {
      $response = $this->_response();
      $this->_validate($response);
      return AuthorizeCallbackService::synchronous(Data::CALLBACK_OPEN, $response);
    } catch (Throwable $th) {
      throw new Exception($th->getMessage(), 1);
    }
  }

  // callback_notification
  //? This is a follow up system callback, used to update the order status, if previous state was "open", or to verify fail/ok status
  //! This callback has a timeout of 5 secounds, therefore the heavy handling is done in a queue job
  public function actionNotification(): void
  {
    $response = $this->_response();
    QueueService::notification($response);
  }

  private function _response()
  {
    $request = Craft::$app->getRequest()->getBodyParams();

    $xml = simplexml_load_string($request['xml'], 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) throw new Exception('Failed to parse XML response', 1);
    unset($request['xml']);

    $meta = json_decode(json_encode($xml), true);
    unset($meta['@attributes']);

    $response = Utils::objectify($request);
    $response->meta = Utils::objectify($meta);

    return $response;
  }

  private function _validate($response)
  {
    //TODO: Extend this to check more validation
    if (!$response->transaction_info->order) {
      throw new Exception('Invalid response: Missing order reference', 1);
    }
  }
}
