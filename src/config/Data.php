<?php

namespace QD\altapay\config;

abstract class Data
{
  const NULL_STRING = 'null';

  const CALLBACK_OK = 'ok';
  const CALLBACK_FAIL = 'fail';
  const CALLBACK_OPEN = 'open';
  const CALLBACK_NOTIFICATION = 'notification';

  const RESPONSE_SUCCESS = 'Success';
  const RESPONSE_FAIL = 'Fail';
  const RESPONSE_OPEN = 'Open';
  const RESPONSE_ERROR = 'Error';
  const RESPONSE_PARTIAL_SUCCESS = 'PartialSuccess';

  const PAYMENT_REQUEST_TYPE_PAYMENT = 'payment';
  const PAYMENT_REQUEST_TYPE_PAYMENT_CAPTURE = 'paymentAndCapture';
  const PAYMENT_REQUEST_TYPE_VERIFY = 'verifyCard';
  const PAYMENT_REQUEST_TYPE_CREDIT = 'credit';
  const PAYMENT_REQUEST_TYPE_SUBSCRIPTION = 'subscription';
  const PAYMENT_REQUEST_TYPE_SUBSCRIPTION_CHARGE = 'subscriptionAndCharge';
  const PAYMENT_REQUEST_TYPE_SUBSCRIPTION_RESERVE = 'subscriptionAndReserve';
}
