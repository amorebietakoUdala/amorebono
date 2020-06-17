<?php

namespace App\Entity;

use App\Repository\BonusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=BonusRepository::class)
 * @Serializer\ExclusionPolicy("all")
 */
class Bonus
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
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     */
    private $emandakoak;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $guztira;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $pertsonakoGehienezkoKopurua;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose()
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=Selling::class, mappedBy="bonus")
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEmandakoak(): ?int
    {
        return $this->emandakoak;
    }

    public function setEmandakoak(?int $emandakoak): self
    {
        $this->emandakoak = $emandakoak;

        return $this;
    }

    public function getGuztira(): ?int
    {
        return $this->guztira;
    }

    public function setGuztira(int $guztira): self
    {
        $this->guztira = $guztira;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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
            $selling->setBonus($this);
        }

        return $this;
    }

    public function removeSelling(Selling $selling): self
    {
        if ($this->sellings->contains($selling)) {
            $this->sellings->removeElement($selling);
            // set the owning side to null (unless already changed)
            if ($selling->getBonus() === $this) {
                $selling->setBonus(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->type;
    }

    public function getRemaining(): int
    {
        return $this->guztira - $this->emandakoak;
    }

    public function getPertsonakoGehienezkoKopurua()
    {
        return $this->pertsonakoGehienezkoKopurua;
    }

    public function setPertsonakoGehienezkoKopurua($pertsonakoGehienezkoKopurua)
    {
        $this->pertsonakoGehienezkoKopurua = $pertsonakoGehienezkoKopurua;

        return $this;
    }
}
