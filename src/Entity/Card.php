<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraint as Assert;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCard", "getPlay"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["getCard", "getPlay"])]
    private ?int $value = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCard", "getPlay"])]
    private ?string $family = null;

    #[ORM\Column(length: 8000)]
    #[Groups(["getCard", "getPlay"])]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToMany(targetEntity: GameMod::class, inversedBy: 'cards')]
    private Collection $gamemod;

    public function __construct()
    {
        $this->gamemod = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, GameMod>
     */
    public function getGamemod(): Collection
    {
        return $this->gamemod;
    }

    public function addGamemod(GameMod $gamemod): self
    {
        if (!$this->gamemod->contains($gamemod)) {
            $this->gamemod->add($gamemod);
        }

        return $this;
    }

    public function removeGamemod(GameMod $gamemod): self
    {
        $this->gamemod->removeElement($gamemod);

        return $this;
    }
}
