<?php

namespace QD\altapay\controllers;

use Craft;
use craft\web\Controller;
use QD\altapay\config\Data;
use QD\altapay\config\Utils;
use QD\altapay\domains\authorize\AuthorizeCallbackService;

use Exception;
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
  public function actionNotification()
  {
    //TODO: Handle notification callback
    $response = $this->_response();

    echo '<pre>';
    print_r($response);
    echo '</pre>';
    die;
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


  // [shop_orderid] => DK11910936
  // [currency] => 208
  // [transaction_info] => Array
  //         [store] => 1
  //         [order] => 2254977
  //         [transaction] => 

  // [type] => payment
  // [embedded_window] => 0
  // [amount] => 119.00
  // [transaction_id] => 54930331
  // [payment_id] => 7941ece0-4ee3-4546-9040-f98459c72907
  // [nature] => CreditCard
  // [require_capture] => true
  // [payment_status] => preauth
  // [masked_credit_card] => 411100******0512
  // [blacklist_token] => cdfd04b27b5989e88789200340dedadd85a12895
  // [credit_card_token] => kllngW4EhvbXwWbKoQYroZmy/gCLJOSgQyk2lcwHchE0DmkEwBrkys9bRVGqNX5yBxQ/bxFGivCsHRn6/bX4Tw==+1
  // [fraud_risk_score] => 18
  // [fraud_explanation] => For the test fraud service the risk score is always equal mod 101 of the created amount for the payment
  // [fraud_recommendation] => Deny
  // [status] => succeeded

  //     [@attributes] => Array
  //             [version] => 20170228

  //     [Header] => Array
  //             [Date] => 2025-07-29T11:27:23+02:00
  //             [Path] => API/reservationOfFixedAmount
  //             [ErrorCode] => 0
  //             [ErrorMessage] => Array
  //     [Body] => Array
  //             [Result] => Success
  //             [Transactions] => Array
  //                     [Transaction] => Array
  //                             [TransactionId] => 54930331
  //                             [PaymentId] => 7941ece0-4ee3-4546-9040-f98459c72907
  //                             [AuthType] => payment
  //                             [CardStatus] => InvalidLuhn
  //                             [CreditCardExpiry] => Array
  //                                     [Year] => 2030
  //                                     [Month] => 12
  //                             [CreditCardToken] => kllngW4EhvbXwWbKoQYroZmy/gCLJOSgQyk2lcwHchE0DmkEwBrkys9bRVGqNX5yBxQ/bxFGivCsHRn6/bX4Tw==+1
  //                             [CreditCardMaskedPan] => 411100******0512
  //                             [IsTokenized] => false
  //                             [CardInformation] => Array
  //                                     [IsTokenized] => false
  //                                     [Token] => kllngW4EhvbXwWbKoQYroZmy/gCLJOSgQyk2lcwHchE0DmkEwBrkys9bRVGqNX5yBxQ/bxFGivCsHRn6/bX4Tw==+1
  //                                     [MaskedPan] => 411100******0512
  //                                     [Expiry] => Array
  //                                             [Year] => 2030
  //                                             [Month] => 12
  //                                     [IssuingCountry] => US
  //                                     [LastFourDigits] => 0512
  //                                     [Scheme] => VISA
  //                             [ThreeDSecureResult] => Successful
  //                             [LiableForChargeback] => Issuer
  //                             [CVVCheckResult] => Not_Attempted
  //                             [BlacklistToken] => cdfd04b27b5989e88789200340dedadd85a12895
  //                             [ShopOrderId] => DK11910936
  //                             [Shop] => Quantity
  //                             [Terminal] => Quantity Credit Card Test Terminal
  //                             [TransactionStatus] => preauth
  //                             [ReasonCode] => NONE
  //                             [MerchantCurrency] => 208
  //                             [MerchantCurrencyAlpha] => DKK
  //                             [CardHolderCurrency] => 208
  //                             [CardHolderCurrencyAlpha] => DKK
  //                             [ReservedAmount] => 119.00
  //                             [CapturedAmount] => 0.00
  //                             [RefundedAmount] => 0.00
  //                             [CreditedAmount] => 0.00
  //                             [RecurringDefaultAmount] => 0.00
  //                             [SurchargeAmount] => 0.00
  //                             [CreatedDate] => 2025-07-29 11:27:23
  //                             [UpdatedDate] => 2025-07-29 11:27:23
  //                             [PaymentNature] => CreditCard
  //                             [PaymentSource] => eCommerce
  //                             [PaymentSchemeName] => VISA
  //                             [AuthorisationExpiryDate] => Array
  //                             [PaymentNatureService] => Array
  //                                     [@attributes] => Array
  //                                             [name] => SoapTestAcquirer
  //                                     [SupportsRefunds] => true
  //                                     [SupportsRelease] => true
  //                                     [SupportsMultipleCaptures] => true
  //                                     [SupportsMultipleRefunds] => true
  //                             [FraudRiskScore] => 18
  //                             [FraudExplanation] => For the test fraud service the risk score is always equal mod 101 of the created amount for the payment
  //                             [FraudRecommendation] => Deny
  //                             [ChargebackEvents] => Array
  //                             [PaymentInfos] => Array
  //                                     [PaymentInfo] => Array
  //                                             [0] => 1
  //                                             [1] => 2254977
  //                                             [2] => Array
  //                                                     [@attributes] => Array
  //                                                             [name] => transaction

  //                             [CustomerInfo] => Array
  //                                     [UserAgent] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36
  //                                     [IpAddress] => 62.66.182.154
  //                                     [Email] => ma@quantity.dk
  //                                     [Username] => 5
  //                                     [AccountIdentifier] => Array
  //                                     [Firstname] => Quantity
  //                                     [Lastname] => Digital
  //                                     [CardHolderName] => Quantity Digital
  //                                     [CustomerPhone] => Array
  //                                     [OrganisationNumber] => Array
  //                                     [CountryOfOrigin] => Array
  //                                             [Country] => US
  //                                             [Source] => CardNumber

  //                                     [BillingAddress] => Array
  //                                             [Firstname] => Quantity
  //                                             [Lastname] => Digital
  //                                             [Address] => Array
  //                                             [City] => Array
  //                                             [Region] => Array
  //                                             [Country] => Array
  //                                             [PostalCode] => Array

  //                             [RecipientInfo] => Array
  //                                     [UserAgent] => Array
  //                                     [IpAddress] => Array
  //                                     [Email] => Array
  //                                     [Username] => Array
  //                                     [AccountIdentifier] => Array
  //                                     [Firstname] => Array
  //                                     [Lastname] => Array
  //                                     [CardHolderName] => Array
  //                                     [CustomerPhone] => Array
  //                                     [OrganisationNumber] => Array
  //                                     [CountryOfOrigin] => Array
  //                                             [Country] => Array
  //                                             [Source] => NotSet
  //                             [ReconciliationIdentifiers] => Array
}
