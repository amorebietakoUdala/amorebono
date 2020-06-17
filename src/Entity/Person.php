<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 * @Serializer\ExclusionPolicy("all")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $NAN;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $izena;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $abizenak;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $telefonoa;

    /**
     * @ORM\OneToMany(targetEntity=Selling::class, mappedBy="person")
     */
    private $sellings;

    public function __construct()
    {
        $this->sellings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNAN(): ?string
    {
        return $this->NAN;
    }

    public function setNAN(string $NAN): self
    {
        $this->NAN = $NAN;

        return $this;
    }

    public function getIzena(): ?string
    {
        return $this->izena;
    }

    public function setIzena(string $izena): self
    {
        $this->izena = $izena;

        return $this;
    }

    public function getAbizenak(): ?string
    {
        return $this->abizenak;
    }

    public function setAbizenak(string $abizenak): self
    {
        $this->abizenak = $abizenak;

        return $this;
    }

    public function getTelefonoa(): ?string
    {
        return $this->telefonoa;
    }

    public function setTelefonoa(?string $telefonoa): self
    {
        $this->telefonoa = $telefonoa;

        return $this;
    }

    /**
     * @return Collection|Selling[]
     */
    public function getSellings(): Collection
    {
        return $this->sellings;
    }

    public function addSelling(Selling $selling): self
    {
        if (!$this->sellings->contains($selling)) {
            $this->sellings[] = $selling;
            $selling->setPerson($this);
        }

        return $this;
    }

    public function removeSelling(Selling $selling): self
    {
        if ($this->sellings->contains($selling)) {
            $this->sellings->removeElement($selling);
            // set the owning side to null (unless already changed)
            if ($selling->getPerson() === $this) {
                $selling->setPerson(null);
            }
        }

        return $this;
    }

    public function getErositakoBonuKopurua(Bonus $bonus)
    {
        $boughtBonuses = 0;
        foreach ($this->sellings as $actualBonus) {
            if ($actualBonus->getBonus() === $bonus) {
                $boughtBonuses += $actualBonus->getQuantity();
            }
        }

        return $boughtBonuses;
    }

    public function canBuy(Bonus $bonus, $quantity)
    {
        $maxBonuses = $bonus->getPertsonakoGehienezkoKopurua();
        if ($this->getErositakoBonuKopurua($bonus) + $quantity > $maxBonuses) {
            return false;
        }

        return true;
    }
}
