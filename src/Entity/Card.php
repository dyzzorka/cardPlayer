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
    #[Groups(["getCard"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["getCard"])]
    private ?int $value = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCard"])]
    private ?string $family = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToMany(targetEntity: GameMod::class, inversedBy: 'cards')]
    private Collection $gamemod;

    #[ORM\OneToMany(mappedBy: 'card', targetEntity: Picture::class, orphanRemoval: true)]
    private Collection $image;

    public function __construct()
    {
        $this->gamemod = new ArrayCollection();
        $this->image = new ArrayCollection();
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

    /**
     * @return Collection<int, Picture>
     */
    public function getImage(): Collection
    {
        return $this->image;
    }

    public function addImage(Picture $image): self
    {
        if (!$this->image->contains($image)) {
            $this->image->add($image);
            $image->setCard($this);
        }

        return $this;
    }

    public function removeImage(Picture $image): self
    {
        if ($this->image->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getCard() === $this) {
                $image->setCard(null);
            }
        }

        return $this;
    }
}
