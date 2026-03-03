<?php

namespace AMREU\NikBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NikService
{

   public const NIK_SERVICE_PATHS = [
      'invitation' => '/v1/invitation',
      'login' => '/v1/login',
      'threads' => '/v1/threads',
      'credentials' => '/v1/credentials',
      'checkConnReq' => '/v1/checkConnReq',
   ];

   public function __construct(
      private HttpClientInterface $httpClient,
      private LoggerInterface $logger,
      private string $apiKey,
      private string $endPoint,
   )
   {
   }

    private function requestJson(string $method, string $path, array $options = []): ?array
    {
        $options['headers'] = array_merge(
            [
                'X-APP-APIKEY'  => $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            $options['headers'] ?? []
        );

        $url = rtrim($this->endPoint, '/').'/'.ltrim($path, '/');

        try {
            $response = $this->httpClient->request($method, $url, $options);

            $status = $response->getStatusCode();
            if ($status === Response::HTTP_NO_CONTENT) {
                return null;
            }
            $this->logger->debug("NIKService call successfull: at path: $path method: $method");
            $this->logger->debug('Response:'.$response->getContent(true));
            return $response->toArray(true);

        } catch (HttpExceptionInterface | TransportExceptionInterface | TimeoutExceptionInterface $e) {
            $message = $e->getMessage();
            $this->logger->error("There was an error on NIK Service: $message");
            return null;
        } 
    }

   public function invitation(): array|null
   {
      return $this->requestJson('POST', NikService::NIK_SERVICE_PATHS['invitation']);
   }  

   public function getInvitation(?string $inviId): ?array
   {
      if ($inviId === null || $inviId === '') {
         return null;
      }

      $path = rtrim(NikService::NIK_SERVICE_PATHS['invitation'], '/').'/'.rawurlencode($inviId);
      return $this->requestJson('GET', $path);
   }

   public function login(
      string $connUUID,
      string $petID,
      string $descriptionEs,
      string $descriptionEu,
      string $locale,
      ?string $callback = null
   ): ?array {
      $this->logger->debug("NIKService login started: connUUID:$connUUID, petID:$petID, locale:$locale, callback:$callback");
      if ($connUUID === '' || $petID === '') {
         return null;
      }

      $payload = [
         'connUUID' => $connUUID,
         'desc'     => [
               'es' => $descriptionEs,
               'eu' => $descriptionEu,
         ],
         'petID'    => $petID,
      ];

      if ($callback !== null && $callback !== '') {
         $payload['callback'] = $callback;
      }

      $headers = [
         'Accept'           => 'application/json',
         'Content-Type'     => 'application/json',
         'X-APP-APIKEY'     => $this->apiKey,
         'Accept-Language'  => $locale,
      ];

      return $this->requestJson(
         'POST',
         NikService::NIK_SERVICE_PATHS['login'],
         [
               'headers' => $headers,
               'json'    => $payload,
               'timeout' => 10, // nahi izanez gero
         ]
      );
   }

   
   public function getThread($threadId): array|null {
      if ($threadId === null || $threadId === '') {
         return null;
      }
      $this->logger->debug("NIKService getThread: threadID:$threadId");
      $path = rtrim(NikService::NIK_SERVICE_PATHS['threads'], '/').'/'.rawurlencode($threadId);
      return $this->requestJson('GET', $path);
   } 

   public function checkConnReq($payload, $locale): array|null {
      $this->logger->debug("NIKService checkConnReq: payload:$payload");
      $payload = [
         'payload' => $payload
      ];

      $headers = [
         'Accept'           => 'application/json',
         'Content-Type'     => 'application/json',
         'X-APP-APIKEY'     => $this->apiKey,
         'Accept-Language'  => $locale,
      ];

      return $this->requestJson(
         'POST',
         NikService::NIK_SERVICE_PATHS['checkConnReq'],
         [
               'headers' => $headers,
               'json'    => $payload,
               'timeout' => 10, // nahi izanez gero
         ]
      );
      return $this->requestJson('POST', $path);
   }

   // public function getCredentials($credentialId): array|null {
   //    $body = null;
   //    $response = $this->httpClient->request('GET',"$this->endPoint".NikService::NIK_SERVICE_PATHS['credentials']."/$credentialId",[
   //       'headers' => [
   //          'X-APP-APIKEY' => $this->apiKey,
   //          'Content-Type' => 'application/json'
   //       ]
   //    ]);
   //    if ( $response->getStatusCode() === Response::HTTP_OK || $response->getStatusCode() === Response::HTTP_ACCEPTED ) {
   //       $body = $response->getContent();
   //       return json_decode($body, true);
   //    }

   //    return $body;
   // } 
}
