<?php

namespace App\DTO;

final class BonosDisponiblesResponse {

   public function __construct(
      private int $error,
      private string $msg,
      private int $restantesTipo1,
      private int $restantesTipo2,
      private int $sinvalidar,
   )
   {
   }

   public static function createFromArray(array $data): self
   {
      return new self(
         $data['error'] ?? 0,
         $data['msg'] ?? '',
         $data['restantes_tipo1'] ?? '',
         $data['restantes_tipo2'] ?? '',
         $data['sinvalidar'] ?? '',
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(error=%d, msg=%s, restantesTipo1=%s, restantesTipo2=%s, sinvalidar=%s)",
         $this->error,
         $this->msg,
         $this->restantesTipo1,
         $this->restantesTipo2,
         $this->sinvalidar,
      );
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
}