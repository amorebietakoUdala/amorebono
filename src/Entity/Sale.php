<?php

namespace App\Entity;

use App\DTO\Bono;
use App\Repository\SaleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $dni = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $qr = null;

    #[ORM\Column(length: 255)]
    private ?string $codigo = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoBono = null;

    #[ORM\Column]
    private ?\DateTime $fechaCaducidad = null;

    #[ORM\Column]
    private ?\DateTime $fecha = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getQr(): ?string
    {
        return $this->qr;
    }

    public function setQr(string $qr): static
    {
        $this->qr = $qr;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getTipoBono(): ?string
    {
        return $this->tipoBono;
    }

    public function setTipoBono(string $tipoBono): static
    {
        $this->tipoBono = $tipoBono;

        return $this;
    }

    public function getFechaCaducidad(): ?\DateTime
    {
        return $this->fechaCaducidad;
    }

    public function setFechaCaducidad(\DateTime $fechaCaducidad): static
    {
        $this->fechaCaducidad = $fechaCaducidad;

        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function fillBono (Bono $bono): self 
    {
        $this->qr = $bono->getQr();
        $this->codigo = $bono->getCodigo();
        $this->tipoBono = $bono->getTipoBono();
        $this->fechaCaducidad = $bono->getFechaCaducidad();
        return $this;
    }
}
