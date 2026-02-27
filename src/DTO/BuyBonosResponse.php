<?php

namespace App\DTO;

use App\Service\AmorebonoService;

final class BuyBonosResponse {

   private int $restantesTipo1 = 0;
   private int $restantesTipo2 = 0;
   private int $quedanTipoBono1 = 0;
   private int $quedanTipoBono2 = 0;
   private int $sinvalidar = 0;
   private string|null $pdf = null;
   private array $bonos = [];

   public function __construct(
      private int $error,
      private string $msg,
      $restantesTipo1,
      $restantesTipo2,
      $quedanTipoBono1,
      $quedanTipoBono2,
      $sinvalidar,
      $pdf,
      $bonos,
   )
   {
      $this->restantesTipo1 = $restantesTipo1;
      $this->restantesTipo2 = $restantesTipo2;
      $this->quedanTipoBono1 = $quedanTipoBono1;
      $this->quedanTipoBono2 = $quedanTipoBono2;
      $this->sinvalidar = $sinvalidar;
      $this->pdf = $pdf;
      $this->bonos = $bonos;
   }

   public static function createFromArray(array $data): self
   {
      $bonos = [];
      if (isset($data['bonos']) && is_array($data['bonos'])) {
         foreach ($data['bonos'] as $bonoData) {
            dump($bonoData);
            $bonos[] = Bono::createFromArray($bonoData);
         }
      }      
      return new self(
         $data['error'] ?? 0,
         $data['msg'] ?? '',
         $data['restantes_tipo1'] ?? 0,
         $data['restantes_tipo2'] ?? 0,
         $data['quedan_bonos_tipo_1'] ?? 0,
         $data['quedan_bonos_tipo_2'] ?? 0,
         $data['sinvalidar'] ?? 0,
         $data['pdf'] ?? null,
         $bonos
      );
   }

   public function __toString()
   {


      return sprintf(
         "Campaign(error=%d, msg=%s, restantesTipo1=%s, restantesTipo2=%s, sinvalidar=%s, quedanTipoBono1=%s, quedanTipoBono2=%s, bonos=%s)",
         $this->error,
         $this->msg,
         $this->restantesTipo1,
         $this->restantesTipo2,
         $this->quedanTipoBono1,
         $this->quedanTipoBono2,
         $this->sinvalidar,
         json_encode($this->bonos),
      );
   }

   public function getError()
   {
         return $this->error;
   }

   public function setError($error)
   {
         $this->error = $error;

         return $this;
   }

   public function getMsg()
   {
         return $this->msg;
   }

   public function setMsg($msg)
   {
         $this->msg = $msg;

         return $this;
   }


   public function getRestantesTipo1(): int
   {
      return $this->restantesTipo1;
   }

   public function setRestantesTipo1($restantesTipo1): self
   {
      $this->restantesTipo1 = $restantesTipo1;

      return $this;
   }

   public function getRestantesTipo2(): int
   {
      return $this->restantesTipo2;
   }

   public function setRestantesTipo2($restantesTipo2): self
   {
      $this->restantesTipo2 = $restantesTipo2;

      return $this;
   }

   public function getSinvalidar(): int
   {
      return $this->sinvalidar;
   }

   public function setSinvalidar($sinvalidar): self
   {
      $this->sinvalidar = $sinvalidar;

      return $this;
   }

   public function getDisponibles(): array {
      return [
         'restantes_tipo1' => $this->restantesTipo1,
         'restantes_tipo2' => $this->restantesTipo2,
      ];
   }

   public function isOk(): bool {
      return $this->error === AmorebonoService::RESPONSE_OK;
   }

   public function getQuedanTipoBono1(): int
   {
      return $this->quedanTipoBono1;
   }

   public function setQuedanTipoBono1($quedanTipoBono1): self
   {
      $this->quedanTipoBono1 = $quedanTipoBono1;

      return $this;
   }

   public function getQuedanTipoBono2(): int
   {
      return $this->quedanTipoBono2;
   }

   public function setQuedanTipoBono2($quedanTipoBono2): self 
   {
      $this->quedanTipoBono2 = $quedanTipoBono2;

      return $this;
   }

   public function getPdf()
   {
      return $this->pdf;
   }

   public function setPdf($pdf)
   {
      $this->pdf = $pdf;

      return $this;
   }

   public function getBinaryPdf() {
      $binary = base64_decode($this->pdf, true);
      if ($binary === false) {
         throw new \RuntimeException('PDF base64 inválido');
      }

      return $binary;
   }

   /**
    * @return Bono[]
   */
   public function getBonos(): array
   {
      return $this->bonos;
   }

   public function setBonos($bonos): self
   {
      $this->bonos = $bonos;

      return $this;
   }
}