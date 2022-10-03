<?php

namespace App\Entity;

use App\Repository\RankRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankRepository::class)]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ranks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\Column(nullable: true)]
    private ?int $mmr = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'ranks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMod $gamemod = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getMmr(): ?int
    {
        return $this->mmr;
    }

    public function setMmr(?int $mmr): self
    {
        $this->mmr = $mmr;

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

    public function getGamemod(): ?GameMod
    {
        return $this->gamemod;
    }

    public function setGamemod(?GameMod $gamemod): self
    {
        $this->gamemod = $gamemod;

        return $this;
    }
}
