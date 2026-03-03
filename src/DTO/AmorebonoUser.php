<?php

namespace App\DTO;

final class AmorebonoUser {

   private string $dni;
   private ?string $givenName = null;
   private ?string $surname1 = null;
   private ?string $surname2 = null;

   public function __construct(
      string $dni,
      string|null $givenName = null,
      string|null $surname1 = null,
      string|null $surname2 = null,
   ) 
   {
      $this->dni = $dni;
      $this->givenName = $givenName;
      $this->surname1 = $surname1;
      $this->surname2 = $surname2;
   }

   public function getDni()
   {
      return $this->dni;
   }

   public function setDni($dni)
   {
      $this->dni = $dni;

      return $this;
   }

   public function getGivenName()
   {
         return $this->givenName;
   }

   public function setGivenName($givenName)
   {
         $this->givenName = $givenName;

         return $this;
   }

   public function getSurname1()
   {
         return $this->surname1;
   }

   public function setSurname1($surname1)
   {
         $this->surname1 = $surname1;

         return $this;
   }

   public function getSurname2()
   {
         return $this->surname2;
   }

   public function setSurname2($surname2)
   {
         $this->surname2 = $surname2;

         return $this;
   }


   public static function createFromArray(array $data): self
   {
      return new self(
         $data['dni'] ?? '',
         $data['givenName'] ?? '',
         $data['surname1'] ?? '',
         $data['surname2'] ?? '',
      );
   }

   public function __toString()
   {
      return sprintf(
         "Campaign(dni=%d, givenName=%s, surname1=%s, surname2=%s)",
         $this->dni,
         $this->givenName,
         $this->surname1,
         $this->surname2,
      );
   }

}
