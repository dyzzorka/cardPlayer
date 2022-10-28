<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Groups;

class BlackJack
{
    private array $deck = [];
    #[Groups(["getPlay"])]
    private array $players = [];
    #[Groups(["getPlay"])]
    private Player $actualPlayer;
    #[Groups(["getPlay"])]
    private Player $nextPlayer;

    public function __construct(Party $party)
    {
        foreach ($party->getUsers() as $user) {
            $player = new Player();
            $this->addPlayers($player->setUser($user));
        }
        $this->addPlayers(new Croupier());
        $this->actualPlayer = $this->players[0];
        $this->nextPlayer = $this->players[1];
    }

    public function getDeck(): array
    {
        return $this->deck;
    }

    public function setDeck(array $deck): self
    {
        $this->deck = $deck;
        return $this;
    }

    public function addDeck(Card $card): self
    {
        array_push($this->deck, $card);

        return $this;
    }

    // public function removeDeck(Card $card): self
    // {

    //     array_splice($this->deck, array_search($this->deck, $card), 1);
    //     return $this;
    // }

    /**
     * @return Collection<int, Card>
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    public function addPlayers(Player $player): self
    {
        array_push($this->players, $player);

        return $this;
    }

    // public function removePlayers(Player $player): self
    // {
    //     $this->players->removeElement($player);
    //     return $this;
    // }


    /**
     * Get the value of actualPlayer
     */
    public function getActualPlayer()
    {
        return $this->actualPlayer;
    }

    /**
     * Set the value of actualPlayer
     *
     * @return  self
     */
    public function setActualPlayer($actualPlayer)
    {
        $this->actualPlayer = $actualPlayer;

        return $this;
    }

    /**
     * Get the value of nextPlayer
     */
    public function getNextPlayer()
    {
        return $this->nextPlayer;
    }

    /**
     * Set the value of nextPlayer
     *
     * @return  self
     */
    public function setNextPlayer($nextPlayer)
    {
        $this->nextPlayer = $nextPlayer;

        return $this;
    }
}

class Player
{
    #[Groups(["getPlay"])]
    private User $user;
    #[Groups(["getPlay"])]
    private Collection $hand;

    public function __construct()
    {
        $this->hand = new ArrayCollection();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getHand(): Collection
    {
        return $this->hand;
    }

    public function addHand(Card $hand): self
    {
        if (!$this->hand->contains($hand)) {
            $this->hand->add($hand);
        }

        return $this;
    }

    public function removeHand(Card $hand): self
    {
        $this->hand->removeElement($hand);
        return $this;
    }
}

class Croupier extends Player
{
    #[Groups(["getPlay"])]
    private string $username = "Croupier";


    public function __construct()
    {
        $this->hand = new ArrayCollection();
    }

    /**
     * @return Collection<int, Card>
     */
    public function getHand(): Collection
    {
        return $this->hand;
    }

    public function addHand(Card $hand): self
    {
        if (!$this->hand->contains($hand)) {
            $this->hand->add($hand);
        }

        return $this;
    }

    public function removeHand(Card $hand): self
    {
        $this->hand->removeElement($hand);
        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}