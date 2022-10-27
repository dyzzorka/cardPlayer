<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

class BlackJack
{
    #[Groups(["getPlay"])]
    private array $deck = [];
    #[Groups(["getPlay"])]
    private array $players = [];
    #[Groups(["getPlay"])]
    private Player $actualPlayer;
    #[Groups(["getPlay"])]
    private Player $nextPlayer;

    public function __construct(Party $party)
    {

        $this->doDeck($party->getGamemod()->getCards()->toArray());
        foreach ($party->getUsers() as $user) {
           
            $this->addPlayers(new Player($user));
        }
        $this->actualPlayer = $this->players[0];
    }


    public function getDeck(): array
    {
        return $this->deck;
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

    public function doDeck(array $cards): self
    {
        
        for ($i = 0; $i < 6; $i++) {
            $this->deck = array_merge($this->deck, $cards);
        }
        shuffle($this->deck);
        return $this;
    }
}


class Player
{
    #[Groups(["getPlay"])]
    private User $user;
    #[Groups(["getPlay"])]
    private Collection $hand;

    public function __construct(User $user)
    {
        
        $this->user = $user;
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
