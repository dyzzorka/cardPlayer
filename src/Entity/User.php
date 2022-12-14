<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUser", "registerResponse", "getRank", "getOneRank", "getParty", "getPartyHistory", "getPlay","getPartyHistoryByParty"])]
    private ?int $id = null;
    
    #[Groups(["getUser", "registerResponse", "getRank", "getOneRank", "getParty", "getParty", "getPartyHistory", "getPlay","getPartyHistoryByParty"])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rank::class)]
    #[Groups(["getUser"])]
    private Collection $ranks;

    #[ORM\ManyToMany(targetEntity: Party::class, inversedBy: 'users')]
    private Collection $parties;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PartyHistory::class, orphanRemoval: true)]
    #[Groups(["getPartyHistory"])]
    private Collection $partyHistories;

    public function __construct()
    {
        $this->ranks = new ArrayCollection();
        $this->parties = new ArrayCollection();
        $this->partyHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $rank->setUser($this);
        }

        return $this;
    }

    public function removeRank(Rank $rank): self
    {
        if ($this->ranks->removeElement($rank)) {
            // set the owning side to null (unless already changed)
            if ($rank->getUser() === $this) {
                $rank->setUser(null);
            }
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
        }

        return $this;
    }

    public function removeParty(Party $party): self
    {
        $this->parties->removeElement($party);

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
            $partyHistory->setUser($this);
        }

        return $this;
    }

    public function removePartyHistory(PartyHistory $partyHistory): self
    {
        if ($this->partyHistories->removeElement($partyHistory)) {
            // set the owning side to null (unless already changed)
            if ($partyHistory->getUser() === $this) {
                $partyHistory->setUser(null);
            }
        }

        return $this;
    }
}
