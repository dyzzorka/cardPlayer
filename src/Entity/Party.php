<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartyRepository::class)]
class Party
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $token = null;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'parties')]
    private Collection $players;

    #[ORM\ManyToOne(inversedBy: 'parties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMod $gamemod = null;

    #[ORM\Column]
    private ?bool $run = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getGamemod(): ?GameMod
    {
        return $this->gamemod;
    }

    public function setGamemod(?GameMod $gamemod): self
    {
        $this->gamemod = $gamemod;

        return $this;
    }

    public function isRun(): ?bool
    {
        return $this->run;
    }

    public function setRun(bool $run): self
    {
        $this->run = $run;

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
}
