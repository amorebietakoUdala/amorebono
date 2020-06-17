<?php

namespace App\Entity;

use App\Repository\SellingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SellingRepository::class)
 */
class Selling
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="sellings", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private $totalPrice;

    /**
     * @ORM\ManyToOne(targetEntity=Bonus::class, inversedBy="sellings", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $bonus;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $sellingDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $serialNumber;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sellings")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getBonus(): ?Bonus
    {
        return $this->bonus;
    }

    public function setBonus(?Bonus $bonus): self
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getSellingDate(): ?\DateTimeInterface
    {
        return $this->sellingDate;
    }

    public function setSellingDate($sellingDate): self
    {
        $this->sellingDate = $sellingDate;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
