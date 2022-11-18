<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Groups;

class BlackJack
{ 

    private Party $party;

    private array $deck = [];
    #[Groups(["getPlay"])]
    private array $players = [];
    #[Groups(["getPlay"])]
    private ?Player $actualPlayer;
    #[Groups(["getPlay"])]
    private ?Player $nextPlayer;

    public function __construct(Party $party)
    {
        $this->party = $party;
        
        foreach ($party->getUsers() as $user) {
            $player = new Player();
            $this->addPlayers($player->setUser($user));
        }
        $this->addPlayers(new Croupier());
        $this->actualPlayer = $this->players[0];
        $this->nextPlayer = $this->players[1];
    }

    /**
     * Get the value of party
     */ 
    public function getParty()
    {
        return $this->party;
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
 
    public function getPlayers(): array
    {
        return $this->players;
    }

    public function addPlayers(Player $player): self
    {
        array_push($this->players, $player);

        return $this;
    }

    public function setPlayers(array $players): self
    {
        $this->players = $players;
        return $this;
    }

    public function removePlayers(Player $player): self
    {
        unset($this->players[array_search($player, $this->players)]);
        return $this;
    }


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

    private ?string $choice; 

    private ?string $resultGame;

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

    public function setHand(Collection $hand): Player
    {
        $this->hand = $hand;
        return $this;
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
     * Get the value of choice
     *
     * @return string
     */
    public function getChoice(): string
    {
        return $this->choice;
    }

    /**
     * Set the value of choice
     *
     * @param string $choice
     *
     * @return self
     */
    public function setChoice(string $choice): self
    {
        $this->choice = $choice;

        return $this;
    }

        /**
     * Get the value of resultGame
     */ 
    public function getResultGame()
    {
        return $this->resultGame;
    }

    /**
     * Set the value of resultGame
     *
     * @return  self
     */ 
    public function setResultGame($resultGame)
    {
        $this->resultGame = $resultGame;

        return $this;
    }
}

class Croupier extends Player
{
    #[Groups(["getPlay"])]
    private string $username = "Croupier";

    #[Groups(["getPlay"])]
    private Card $backcard;


    public function __construct()
    {
        $this->hand = new ArrayCollection();
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

            /**
     * Get the value of backcard
     */ 
    public function getBackcard()
    {
        return $this->backcard;
    }

    /**
     * Set the value of backcard
     *
     * @return  self
     */ 
    public function setBackcard($backcard)
    {
        $this->backcard = $backcard;

        return $this;
    }

}
