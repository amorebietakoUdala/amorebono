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
   ];

   public function __construct(
      private HttpClientInterface $httpClient,
      private LoggerInterface $logger,
      private string $apiKey,
      private string $endPoint,
   )
   {
   }

    /**
     * HTTP dei orokorra JSON → array bihurtuta.
     *  - 2xx: array|null (204 kasuan null)
     *  - bestela: null edo salbuespena jaurti (behean aukeran)
     */
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

            return $response->toArray(true);

        } catch (HttpExceptionInterface | TransportExceptionInterface | TimeoutExceptionInterface $e) {
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

        $path = rtrim(NikService::NIK_SERVICE_PATHS['threads'], '/').'/'.rawurlencode($threadId);
        return $this->requestJson('GET', $path);
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

   //  #[Route(path: '/nik/success', name: 'amreu_nik_success')]
   //  public function success(Request $request): Response
   //  {
   //      $giltzaUser = $request->getSession()->get("giltzaUser");
   //      if (!$giltzaUser) {
   //          return $this->redirectToRoute('amreu_giltza_login');
   //      }
   //      return $this->json($giltzaUser);
   //  }

   //  #[Route(path: '/logout', name: 'amreu_nik_logout')]
   //  public function logout(Request $request): Response
   //  {
   //      $request->getSession()->invalidate();
   //      return $this->json('logout');
   //  }
}
