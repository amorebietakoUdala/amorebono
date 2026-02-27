<?php

namespace App\DTO;

final class Bono {

   private string $qr;
   private string $codigo;
   private int $paga;
   private int $gasta;
   private string $tipoBono;
   private ?\DateTime $fechaCaducidad = null;

   public function __construct(
      string $qr,
      string $codigo,
      string $paga,
      string $gasta,
      string $tipoBono,
      string $fechaCaducidad,
   ) 
   {
      $this->qr = $qr;
      $this->codigo = $codigo;
      $this->tipoBono = $tipoBono;
      $this->paga =  $paga != null ? intval($paga) : 0;
      $this->gasta =  $gasta != null ? intval($gasta) : 0;
      if ($fechaCaducidad !== null ) {
         $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $fechaCaducidad);
         if ($dateTime !== false) {
            $this->fechaCaducidad = $dateTime;  
         }
      }
   }

   public function getQr()
   {
      return $this->qr;
   }

   public function setQr($qr)
   {
      $this->qr = $qr;

      return $this;
   }

   public function getCodigo()
   {
      return $this->codigo;
   }

   public function setCodigo($codigo)
   {
      $this->codigo = $codigo;

      return $this;
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

   public function getPaga(): string
   {
      return $this->paga;
   }

   public function setPaga(string $paga): self
   {
      $this->paga = $paga;

      return $this;
   }

   public function getGasta(): int
   {
      return $this->gasta;
   }

   public function setGasta($gasta): self
   {
      $this->gasta = $gasta;

      return $this;
   }

   public function getFechaCaducidad(): ?\DateTime
   {
      return $this->fechaCaducidad;
   }

   public function setFechaCaducidad($fechaCaducidad)
   {
      $this->fechaCaducidad = $fechaCaducidad;

      return $this;
   }

   public static function createFromArray(array $data): self
   {
      return new self(
         $data['qr'] ?? '',
         $data['codigo'] ?? '',
         $data['paga'] ?? '',
         $data['gasta'] ?? '',
         $data['tipo'] ?? '',
         $data['caducidad'] ?? '',
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(qr=%d, codigo=%s, tipo=%s, paga=%s, gasta=%s, fechaCaducidad=%s)",
         $this->qr,
         $this->codigo,
         $this->tipoBono,
         $this->paga,
         $this->gasta,
         $this->fechaCaducidad,
      );
   }

}
