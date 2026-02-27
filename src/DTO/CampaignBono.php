<?php

namespace App\DTO;

final class CampaignBono {

   private int $stockTotal;
   private int $consumidos;
   private int $maxPorPersona;

   public function __construct(
      private string $tipoBono,
      private string $nombreEs,
      private string $nombreEu,
      private string $paga,
      string $maxPorPersona = "0",
      private string $gasta,
      string $stockTotal = "0",
      string $consumidos = "0",
   ) 
   {
      $this->stockTotal =  $stockTotal != null ? intval($stockTotal) : 0;
      $this->consumidos =  $consumidos != null ? intval($consumidos) : 0;
      $this->maxPorPersona =  $maxPorPersona != null ? intval($maxPorPersona) : 0;
   }

   public function getTipoBono(): string
   {
      return $this->tipoBono;
   }

   public function setTipoBono(string $tipoBono): self
   {
      $this->tipoBono = $tipoBono;

      return $this;
   }

   public function getNombreEs(): string
   {
      return $this->nombreEs;
   }

   public function setNombreEs(string $nombreEs): self
   {
      $this->nombreEs = $nombreEs;

      return $this;
   }

   public function getNombreEu(): string
   {
      return $this->nombreEu;
   }

   public function setNombreEu(string $nombreEu): self
   {
      $this->nombreEu = $nombreEu;

      return $this;
   }

   public function getPaga(): string
   {
      return $this->paga;
   }

   public function setPaga(string $paga): self
   {
      $this->paga = $paga;

      return $this;
   }

   public function getMaxPorPersona(): int
   {
      return $this->maxPorPersona;
   }

   public function setMaxPorPersona(string $maxPorPersona): self
   {
      $this->maxPorPersona = intval($maxPorPersona);

      return $this;
   }

   public function getGasta(): string
   {
      return $this->gasta;
   }

   public function setGasta(string $gasta): self
   {
      $this->gasta = $gasta;

      return $this;
   }
   
   public function getStockTotal(): int
   {
      return $this->stockTotal;
   }

   public function setStockTotal(string $stockTotal): self
   {

      $this->stockTotal = intval($stockTotal);

      return $this;
   }

   public function getConsumidos(): int
   {
      return $this->consumidos;
   }

   public function setConsumidos(string $consumidos): self
   {
      $this->consumidos = intval($consumidos);

      return $this;
   }
   
   public static function createFromArray(array $data): self
   {
      return new self(
         $data['tipo_bono'] ?? '',
         $data['nombre_es'] ?? '',
         $data['nombre_eu'] ?? '',
         $data['paga'] ?? '',
         $data['max_por_persona'] ?? '',
         $data['gasta'] ?? '',
         $data['stock_total'] ?? '',
         $data['consumidos'] ?? ''
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(tipoBono=%d, nombreEs=%s, nombreEu=%s, paga=%s, gasta=%s, maxPorPersona=%s, stockTotal=%s, consumidos=%s)",
         $this->tipoBono,
         $this->nombreEs,
         $this->nombreEu,
         $this->paga,
         $this->gasta,
         $this->maxPorPersona,
         $this->stockTotal,
         $this->consumidos,
      );
   }

}
