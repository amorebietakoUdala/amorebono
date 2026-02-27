<?php

namespace App\Service;

use App\DTO\Campaign;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AmorebonoService
{

   /**
    * ERROR_CODES
    * 1: Se produjo un error al obtener la información de la campaña actual
    * 2: Se produjo un error al obtener la información de la campaña actual
    * 3: El header token tiene datos incorrectos o incompletos
    * 4: Se alcanzó el número de bonos máximo de bonos de la campaña
    * 6: El usuario ha solicitado más bonos de los permitidos o ha rebasado su límite personal.
    *
   */
   
   public const RESPONSE_OK = 0;
   public const ERROR_FETCHING_CAMPAIGN_1 = 1;
   public const ERROR_FETCHING_CAMPAIGN_2 = 2;
   public const ERROR_INCORRECT_TOKEN = 3;
   public const ERROR_CAMPAIGN_MAX_REACHED = 4;
   public const ERROR_PERSONAL_MAX_EXCEEDED = 6;


   public function __construct(
      private HttpClientInterface $client,
      private string $endpointRoot,
      private string $tokenRoot,
      private string $apiKey
   )
   {
   }

   private function getToken($dni = null): string {
      $date = (new \DateTime())->format('Y-m-d');
      if ( null === $dni) {
         return "$this->tokenRoot$date";
      }
      return "$this->tokenRoot$date~$dni";
   }

   public function info(): Campaign
   {
      $response = $this->client->request(
         'GET',
         $this->endpointRoot . '/info',[
            'headers' => [
               'X-api-key' => $this->apiKey,
               'token'  => $this->getToken(),
            ]
         ]
      );

      $campaignArray = $response->toArray();  
      if ($campaignArray['error'] === 0) {
         $campaign = Campaign::createFromArray($campaignArray);
      }

      return $campaign;
   }

   public function buy(string $dni, array $data, bool $pdf = true): array
   {
      $cantidadesBonos = $this->getArrayCantidadBonos($data);
      $response = $this->client->request(
         'GET',
         $this->endpointRoot . '/consumir_bono',[
            'headers' => array_merge(
               $cantidadesBonos,
               [
                  'X-api-key' => $this->apiKey,
                  'token'  => $this->getToken($dni),
                  'pdf' => $pdf,
               ],
            )
         ]
      );

      return $response->toArray();  
   }

   private function getArrayCantidadBonos(array $data): array
   {
      $cantidadesBonos = [];
      $cantidadesBonos["cantidad-bonos1"] = $data["cantidad_bonos1"];
      $cantidadesBonos["cantidad-bonos2"] = $data["cantidad_bonos2"];
      // If a campaign has more than 2 bonos. You have to fix this.
      // foreach ($tiposBono as $bono) {
      //    if ($bono->getTipoBono() === $tipoBono) {
      //       $cantidadesBonos["cantidad-bonos$tipoBono"] = $data["cantidad_bonos$tipoBono"];
      //    } else {
      //       $tipoBono = $bono->getTipoBono();
      //       $cantidadesBonos["cantidad-bonos$tipoBono"] = 0;
      //    }
      // }
      return $cantidadesBonos;
   }

   public function getBonosUsuarioDisponibles(string $dni) {
      $response = $this->client->request(
         'GET',
         $this->endpointRoot . '/nbonos_usuario',[
            'headers' => [
               'X-api-key' => $this->apiKey,
               'token'  => $this->getToken($dni),
            ]
         ]
      );
      return $response->toArray();
   }

   public function reprintBonos(string $dni, bool $pdf = true) {
      $response = $this->client->request(
         'GET',
         $this->endpointRoot . '/reimprimir_bonos',[
            'headers' => [
               'X-api-key' => $this->apiKey,
               'token'  => $this->getToken($dni),
               'pdf' => $pdf,
            ]
         ]
      );
      return $response->toArray();
   }
}
