<?php

namespace App\Entity;

use App\Repository\RankRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 *  @Hateoas\Relation(
 *      "self", href=@Hateoas\Route(
 *          "party.getAll"
 *      ),
 *      exclusion= @Hateoas\Exclusion(groups="getParty")
 *  )
 */
#[ORM\Entity(repositoryClass: RankRepository::class)]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUser", "getRank", "getOneRank"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getUser", "getRank", "getOneRank"])]
    private ?int $mmr = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'ranks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getUser", "getOneRank"])]
    private ?GameMod $gamemod = null;

    #[ORM\ManyToOne(inversedBy: 'ranks')]
    #[Groups(["getRank", "getOneRank"])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
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
