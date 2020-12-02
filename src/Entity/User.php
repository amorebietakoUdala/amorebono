<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use AMREU\UserBundle\Model\User as BaseUser;
use AMREU\UserBundle\Model\UserInterface as AMREUserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser implements AMREUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"}, nullable=true)
     */
    protected $activated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\OneToMany(targetEntity=Selling::class, mappedBy="user")
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
            $selling->setUser($this);
        }

        return $this;
    }

    public function removeSelling(Selling $selling): self
    {
        if ($this->sellings->contains($selling)) {
            $this->sellings->removeElement($selling);
            // set the owning side to null (unless already changed)
            if ($selling->getUser() === $this) {
                $selling->setUser(null);
            }
        }

        return $this;
    }
}
