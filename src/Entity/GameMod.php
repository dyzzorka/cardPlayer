<?php

namespace App\Entity;

use App\Repository\GameModRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 *  @Hateoas\Relation(
 *      "self", href=@Hateoas\Route(
 *          "gamemod.getAll", 
 *          parameters= {"Gamemodname" = "expr(object.getName())" }
 *      ),
 *      exclusion= @Hateoas\Exclusion(groups="getGamemod")
 *  )
 */

#[ORM\Entity(repositoryClass: GameModRepository::class)]
class GameMod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getGamemod", "getUser", "getRank", "getOneRank", "getParty", "getCard","getPartyHistory","getPartyHistoryByParty"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getGamemod", "getUser", "getRank", "getOneRank", "getParty", "getCard","getPartyHistory","getPartyHistoryByParty"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getGamemod", "getCard"])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(["getGamemod", "getParty"])]
    private ?int $player_limit = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'gamemod', targetEntity: Rank::class, orphanRemoval: true)]
    #[ORM\OrderBy(["mmr" => "DESC"])]
    #[Groups(["getRank"])]
    private Collection $ranks;

    #[ORM\ManyToMany(targetEntity: Card::class, mappedBy: 'gamemod')]
    #[Groups(["getCard"])]
    private Collection $cards;

    #[ORM\OneToMany(mappedBy: 'gamemod', targetEntity: Party::class, orphanRemoval: true)]
    private Collection $parties;

    #[ORM\Column(nullable: true)]
    private ?int $player_min = null;

    public function __construct()
    {
        $this->ranks = new ArrayCollection();
        $this->cards = new ArrayCollection();
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPlayerLimit(): ?int
    {
        return $this->player_limit;
    }

    public function setPlayerLimit(int $player_limit): self
    {
        $this->player_limit = $player_limit;

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
     * @return Collection<int, Rank>
     */
    public function getRanks(): Collection
    {
        return $this->ranks;
    }

    public function addRank(Rank $rank): self
    {
        if (!$this->ranks->contains($rank)) {
            $this->ranks->add($rank);
            $rank->setGamemod($this);
        }

        return $this;
    }

    public function removeRank(Rank $rank): self
    {
        if ($this->ranks->removeElement($rank)) {
            // set the owning side to null (unless already changed)
            if ($rank->getGamemod() === $this) {
                $rank->setGamemod(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->addGamemod($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->cards->removeElement($card)) {
            $card->removeGamemod($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Party>
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(Party $party): self
    {
        if (!$this->parties->contains($party)) {
            $this->parties->add($party);
            $party->setGamemod($this);
        }

        return $this;
    }

    public function removeParty(Party $party): self
    {
        if ($this->parties->removeElement($party)) {
            // set the owning side to null (unless already changed)
            if ($party->getGamemod() === $this) {
                $party->setGamemod(null);
            }
        }

        return $this;
    }

    public function getPlayerMin(): ?int
    {
        return $this->player_min;
    }

    public function setPlayerMin(?int $player_min): self
    {
        $this->player_min = $player_min;

        return $this;
    }
}
