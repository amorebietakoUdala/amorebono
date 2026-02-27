<?php

namespace App\DTO;

use App\DTO\CampaignBono;

final class Campaign {
   private int $error;
   private string $msg;
   private string $nombre_es;
   private string $nombre_eu;
   private string $fechaini;
   private string $fechafin;
   private string $duracion_bono_dias;
   private array $bonos;

   public const ERROR_TRUE = 1;
   public const ERROR_FALSE = 0;

   public function __construct(
      int $error,
      string $msg,
      string $nombre_es,
      string $nombre_eu,
      string $fechaini,
      string $fechafin,
      string $duracion_bono_dias,
      array $bonos
   ) {
      $this->error = $error;
      $this->msg = $msg;
      $this->nombre_es = $nombre_es;
      $this->nombre_eu = $nombre_eu;
      $this->fechaini = $fechaini;
      $this->fechafin = $fechafin;
      $this->duracion_bono_dias = $duracion_bono_dias;
      $this->bonos = $bonos;
   }

   public function getError(): int
   {
      return $this->error;
   }

   public function setError(int $error): self
   {
      $this->error = $error;

      return $this;
   }

   public function getMsg(): string
   {
      return $this->msg;
   }

   public function setMsg(string $msg): self
   {
      $this->msg = $msg;

      return $this;
   }

   public function getNombreEs(): string
   {
      return $this->nombre_es;
   }

   public function setNombreEs(string $nombre_es): self
   {
      $this->nombre_es = $nombre_es;

      return $this;
   }

   public function getNombreEu(): string
   {
      return $this->nombre_eu;
   }

   public function setNombreEu(string $nombre_eu): self
   {
      $this->nombre_eu = $nombre_eu;

      return $this;
   }

   public function getFechaini(): string
   {
      return $this->fechaini;
   }

   public function setFechaIni(string $fechaini): self
   {
      $this->fechaini = $fechaini;

      return $this;
   }
   
   public function getFechafin(): string
   {
      return $this->fechafin;
   }

   public function setFechaFin(string $fechafin): self
   {
      $this->fechafin = $fechafin;

      return $this;
   }
      
   public function getDuracionBonoDias(): string
   {
      return $this->duracion_bono_dias;
   }

   public function setDuracionBonoDias(string $duracion_bono_dias): self
   {
      $this->duracion_bono_dias = $duracion_bono_dias;

      return $this;
   }

   public function getBonos(): array
   {
      return $this->bonos;
   }

   public function setBonos(array $bonos): self
   {
      $this->bonos = $bonos;

      return $this;
   }   

   public static function createFromArray(array $data): self
   {
      $bonos = [];
      if (isset($data['bonos']) && is_array($data['bonos'])) {
         foreach ($data['bonos'] as $bonoData) {
            $bonos[] = CampaignBono::createFromArray($bonoData);
         }
      }
      return new self(
         $data['error'] ?? 0,
         $data['msg'] ?? '',
         $data['nombre_es'] ?? '',
         $data['nombre_eu'] ?? '',
         $data['fechaini'] ?? '',
         $data['fechafin'] ?? '',
         $data['duracion_bono_dias'] ?? '',
         $bonos
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(error=%d, msg=%s, nombre_es=%s, nombre_eu=%s, fechaini=%s, fechafin=%s, duracion_bono_dias=%s, bonos=%s)",
         $this->error,
         $this->msg,
         $this->nombre_es,
         $this->nombre_eu,
         $this->fechaini,
         $this->fechafin,
         $this->duracion_bono_dias,
         json_encode($this->bonos)
      );
   }

   public function getNombresBonos($locale = 'eu'): array {
      $descripciones = [];
      foreach ($this->bonos as $bono) {
         if ( $locale === 'es') {
            $descripciones[$bono->getTipoBono()] = $bono->getNombreEs();
         } else {
            $descripciones[$bono->getTipoBono()] = $bono->getNombreEu();
         }
      }

      return $descripciones;
   }

   public function hasBought(BonosDisponiblesResponse $disponibles): bool {
      $bonos = $this->getBonos();
      foreach ($bonos as $bono) {
         if ( $bono->getTipoBono() === '1' && $disponibles->getRestantesTipo1() !==  $bono->getMaxPorPersona() ) {
            return true;
            
         } elseif ( $bono->getTipoBono() === '2' && $disponibles->getRestantesTipo2() !==  $bono->getMaxPorPersona() ) {
            return true;
         }
      }
      return false;
   }
}