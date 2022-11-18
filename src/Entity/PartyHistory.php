<?php

namespace App\Entity;

use App\Repository\PartyHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartyHistoryRepository::class)]
class PartyHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPartyHistory","getPartyHistoryByParty"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'partyHistories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPartyHistory"])]
    private ?Party $party = null;

    #[ORM\ManyToOne(inversedBy: 'partyHistories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getPartyHistoryByParty"])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(["getPartyHistory","getPartyHistoryByParty"])]
    private ?int $gain = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartyHistory","getPartyHistoryByParty"])]
    private ?string $resultGame = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getPartyHistory","getPartyHistoryByParty"])]
    private ?string $optionChoice = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): self
    {
        $this->party = $party;

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

    public function getGain(): ?int
    {
        return $this->gain;
    }

    public function setGain(int $gain): self
    {
        $this->gain = $gain;

        return $this;
    }

    public function getResultGame(): ?string
    {
        return $this->resultGame;
    }

    public function setResultGame(string $resultGame): self
    {
        $this->resultGame = $resultGame;

        return $this;
    }

    public function getOptionChoice(): ?string
    {
        return $this->optionChoice;
    }

    public function setOptionChoice(string $optionChoice): self
    {
        $this->optionChoice = $optionChoice;

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
