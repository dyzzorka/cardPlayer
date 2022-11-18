<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 *  @Hateoas\Relation(
 *      "self", href=@Hateoas\Route(
 *          "party.all"
 *      ),
 *      exclusion= @Hateoas\Exclusion(groups="getParty")
 *  )
 */
#[ORM\Entity(repositoryClass: PartyRepository::class)]
class Party
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    #[Groups(["getParty", "getPartyHistory","getPartyHistoryByParty"])]
    private ?string $token = null;

    #[ORM\ManyToOne(inversedBy: 'parties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getParty", "getPartyHistory","getPartyHistoryByParty"])]
    private ?GameMod $gamemod = null;

    #[ORM\Column]
    private ?bool $run = null;
    
    #[ORM\Column]
    private ?bool $end = null;

    #[ORM\Column]
    #[Groups(["getParty"])]
    private ?bool $full = null;

    #[ORM\Column]
    #[Groups(["getParty", "getPartyHistory","getPartyHistoryByParty"])]
    private ?bool $private = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'parties')]
    #[Groups(["getParty"])]
    private Collection $users;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $advancement = null;

    #[ORM\Column]
    #[Groups(["getParty", "getPartyHistory"])]
    private ?int $bet = null;

    #[ORM\OneToMany(mappedBy: 'party', targetEntity: PartyHistory::class, orphanRemoval: true)]
    #[Groups(["getPartyHistoryByParty"])]
    private Collection $partyHistories;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->partyHistories = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addParty($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeParty($this);
        }

        return $this;
    }

    public function isEnd(): ?bool
    {
        return $this->end;
    }

    public function setEnd(bool $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function isFull(): ?bool
    {
        return $this->full;
    }

    public function setFull(bool $full): self
    {
        $this->full = $full;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getAdvancement(): ?string
    {
        return $this->advancement;
    }

    public function setAdvancement(?string $advancement): self
    {
        $this->advancement = $advancement;

        return $this;
    }

    public function getBet(): ?int
    {
        return $this->bet;
    }

    public function setBet(int $bet): self
    {
        $this->bet = $bet;

        return $this;
    }

    /**
     * @return Collection<int, PartyHistory>
     */
    public function getPartyHistories(): Collection
    {
        return $this->partyHistories;
    }

    public function addPartyHistory(PartyHistory $partyHistory): self
    {
        if (!$this->partyHistories->contains($partyHistory)) {
            $this->partyHistories->add($partyHistory);
            $partyHistory->setParty($this);
        }

        return $this;
    }

    public function removePartyHistory(PartyHistory $partyHistory): self
    {
        if ($this->partyHistories->removeElement($partyHistory)) {
            // set the owning side to null (unless already changed)
            if ($partyHistory->getParty() === $this) {
                $partyHistory->setParty(null);
            }
        }

        return $this;
    }
}
