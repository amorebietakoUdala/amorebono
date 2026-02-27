<?php

namespace App\DTO;

use App\Service\AmorebonoService;

final class ReimprimirBonosResponse {

   public function __construct(
      private int $error,
      private string $msg,
      private string $pdf,
      private array $bonos = [],
   ) {
      $this->error = $error;
      $this->msg = $msg;
      $this->pdf = $pdf;
      $this->bonos = $bonos;
   }


   public static function createFromArray(array $data): self
   {
      $bonos = [];
      if (isset($data['bonos']) && is_array($data['bonos'])) {
         foreach ($data['bonos'] as $bonoData) {
            $bonos[] = Bono::createFromArray($bonoData);
         }
      }
      return new self(
         $data['error'] ?? 0,
         $data['msg'] ?? '',
         $data['pdf'] ?? '',
         $bonos
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(error=%d, msg=%s, pdf=%s, bonos=%s)",
         $this->error,
         $this->msg,
         $this->pdf,
         json_encode($this->bonos),
      );
   }


      public function getError():string
      {
            return $this->error;
      }

      public function setError($error):self
      {
            $this->error = $error;

            return $this;
      }

      public function getMsg():string
      {
            return $this->msg;
      }

      public function setMsg($msg):self
      {
            $this->msg = $msg;

            return $this;
      }

      public function isOk(): bool {
         return $this->error === AmorebonoService::RESPONSE_OK;
      }

      public function getPdf():string
      {
            return $this->pdf;
      }

      public function setPdf($pdf):self
      {
            $this->pdf = $pdf;

            return $this;
      }


      /**
       * @return Bono[]
      */
      public function getBonos():array
      {
            return $this->bonos;
      }

      public function setBonos($bonos):self
      {
            $this->bonos = $bonos;

            return $this;
      }

      public function getBinaryPdf() {
         $binary = base64_decode($this->pdf, true);
         if ($binary === false) {
            throw new \RuntimeException('PDF base64 inválido');
         }

         return $binary;
      }
}