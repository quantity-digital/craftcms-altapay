<?php

namespace QD\altapay\api;

use craft\helpers\App;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use QD\altapay\Altapay;
use QD\altapay\config\Response;
use QD\altapay\config\Utils;

class Api
{
  protected Client $client;
  protected string $baseUrl;
  protected string $username;
  protected string $password;

  public string $method = '';
  public array $params = [];

  public function __construct()
  {
    $settings = Altapay::getInstance()->getSettings();

    $this->username = App::parseEnv($settings->username);
    $this->password = App::parseEnv($settings->password);
    $this->baseUrl = 'https://' . App::parseEnv($settings->shop) . '.altapaysecure.com/merchant.php/API/';

    $this->client = new Client([
      'base_uri' => $this->baseUrl,
    ]);
  }

  /**
   * Set the request method
   *
   * @param string $method
   * @return self
   */
  public function setMethod(string $method): self
  {
    $this->method = $method;
    return $this;
  }

  /**
   * Set form params for request
   *
   * @param array $params
   * @return self
   */
  public function setPayload(array $params): self
  {
    $this->params = $params;
    return $this;
  }

  /**
   * Undocumented function
   *
   * @return Response
   */
  public function get(): Response
  {
    // Validate if method has been defined
    if (!$this->method) throw new Exception('Method not set');

    // Set auth
    $body = ['auth' => [$this->username, $this->password]];

    // Set params if applicable
    if (!empty($this->params)) {
      $body['form_params'] = $this->params;
    }

    // Request
    try {
      // Run get request
      $response = $this->client->get($this->method, $body);

      // Parse XML response
      $xml = simplexml_load_string($response->getBody()->getContents(), 'SimpleXMLElement', LIBXML_NOCDATA);
      if ($xml === false) throw new Exception('Failed to parse AltaPay XML response', 1);

      // Convert XML to PHP array
      $array = json_decode(json_encode($xml), true);

      // Check for errors in the response
      if ($array['Header']['ErrorCode'] !== '0') {
        $errorMessage = $array['Header']['ErrorMessage'] ?? 'Unknown error';
        $errorCode = $array['Header']['ErrorCode'] ?? 'Unknown error code';
        throw new Exception("AltaPay ({$errorCode}): {$errorMessage}", 1);
      }

      // Return
      // Meta
      $meta = [
        'version' => $array['@attributes']['version'] ?? null,
        'date' => $array['Header']['Date'] ?? null,
        'path' => $array['Header']['Path'] ?? null,
      ];

      // Body
      // Unset "Result" as this i moved to the parent int he Response class
      unset($array['Body']['Result']);

      // Convert body to object if needed
      $data = Utils::objectify($array['Body']) ?? null;

      return Response::success($data, $meta);
    } catch (RequestException $e) {
      return Response::error($e->getMessage(), 1);
    }
  }

  public function post(): Response
  {
    // Validate if method has been defined
    if (!$this->method) throw new Exception('Method not set');
    if (!$this->params) throw new Exception('Params not set');

    // Set auth
    $body = [
      'auth' => [$this->username, $this->password],
      'form_params' => $this->params
    ];

    try {
      // Run post request
      $response = $this->client->post($this->method, $body);

      // Parse XML response
      $xml = simplexml_load_string($response->getBody()->getContents(), 'SimpleXMLElement', LIBXML_NOCDATA);
      if ($xml === false) throw new Exception('Failed to parse AltaPay XML response', 1);

      // Convert XML to PHP array
      $array = json_decode(json_encode($xml), true);

      // Check for errors in the response
      if ($array['Header']['ErrorCode'] !== '0') {
        $errorMessage = $array['Header']['ErrorMessage'] ?? 'Unknown error';
        $errorCode = $array['Header']['ErrorCode'] ?? 'Unknown error code';
        throw new Exception("AltaPay ({$errorCode}): {$errorMessage}", 1);
      }

      // Return
      // Meta
      $meta = [
        'version' => $array['@attributes']['version'] ?? null,
        'date' => $array['Header']['Date'] ?? null,
        'path' => $array['Header']['Path'] ?? null,
      ];

      // Body
      // Convert body to object if needed
      $data = Utils::objectify($array['Body']) ?? null;

      return Response::success($data, $meta);
    } catch (RequestException $e) {
      return Response::error($e->getMessage(), 1);
    }
  }
}
