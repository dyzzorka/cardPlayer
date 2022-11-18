<?php

namespace App\Repository;

use App\Entity\BlackJack;
use App\Entity\Croupier;
use App\Entity\Party;
use App\Entity\PartyHistory;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Expr\Cast\Double;
use Symfony\Component\Validator\Constraints\Blank;

/**
 * @extends ServiceEntityRepository<Party>
 *
 * @method Party|null find($id, $lockMode = null, $lockVersion = null)
 * @method Party|null findOneBy(array $criteria, array $orderBy = null)
 * @method Party[]    findAll()
 * @method Party[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartyRepository extends ServiceEntityRepository
{
    private CardRepository $cardkRepo;
    private RankRepository $rankRepository;
    private PartyHistoryRepository $partyHistoryRepository;
    private UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, CardRepository $cardkRepo, RankRepository $rankRepository, PartyHistoryRepository $partyHistoryRepository, UserRepository $userRepository)
    {
        $this->cardkRepo = $cardkRepo;
        $this->partyHistoryRepository = $partyHistoryRepository;
        $this->userRepository = $userRepository;
        $this->rankRepository = $rankRepository;
        parent::__construct($registry, Party::class);
    }

    public function save(Party $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Party $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * function that play the Croupier of the game
     *
     * @param BlackJack $blackJack
     * @return void
     */
    public function playCroupiers(BlackJack $blackJack)
    {
        foreach ($blackJack->getPlayers() as $player) {
            if (get_class($player) == "App\Entity\Croupier") {
                $croupier = $player;
                $croupier->getHand()[1] = $croupier->getBackcard();
            }
        }

        $this->payPlayers($blackJack, $croupier);
    }

    /**
     * function that pay player a the end game and creat and history of this party
     *
     * @param BlackJack $blackJack
     * @param Croupier $croupier
     * @return void
     */
    private function payPlayers(BlackJack $blackJack, Croupier $croupier)
    {
        $this->countLoose($croupier, $blackJack);
        $party = $this->find($blackJack->getParty()->getId());
        $bet = $party->getBet();

        foreach ($blackJack->getPlayers() as $player) {
            if (get_class($player) != "App\Entity\Croupier") {
                $user = $this->userRepository->find($player->getUser()->getId());
                $result = $player->getResultGame();
                $optionChoise = $player->getChoice();
                $gain = ($bet * $this->findGain($player)) - $bet;

                $history = new PartyHistory();
                $this->partyHistoryRepository->save($history->setParty($party)
                    ->setUser($user)
                    ->setGain($gain)
                    ->setResultGame($result)
                    ->setOptionChoice($optionChoise)
                    ->setStatus(true), true);
            }
        }
    }

    /**
     * function that return a coef of the gain
     *
     * @param Player $player
     * @return float
     */
    private function findGain(Player $player): float
    {
        if ($player->getResultGame() == "win") {
            switch ($player->getChoice()) {
                case "classic":
                    return 2.0;
                    break;
                case "blackjack":
                    return 2.5;
                    break;
                case "double":
                    return 4.0;
                    break;
                case "split":
                    return 2.0;
                    break;
                default:
                    return 2.0;
            }
        } else {
            return 0;
        }
    }

    /**
     * function that count and set loose/win in the player
     *
     * @param Croupier $croupier
     * @param BlackJack $blackJack
     * @return integer
     */
    private function countLoose(Croupier $croupier, BlackJack &$blackJack): int
    {
        $countLoose = 0;
        $croupierPoint = $this->countPoint($croupier);
        if ($croupierPoint <= 21) {
            foreach ($blackJack->getPlayers() as $player) {
                if ($this->countPoint($player) < $croupierPoint) {
                    $countLoose++;
                    $player->setResultGame("loose");
                } else {
                    $player->setResultGame("win");
                }
            }
            if ($countLoose == 0) {
                $deck = $blackJack->getDeck();
                $croupier->addHand($this->cardkRepo->pickCard($deck));
                $countLoose = $this->countLoose($croupier, $blackJack);
            }
        }
        return $countLoose;
    }

    /**
     * function that play for the player with his instruction
     *
     * @param BlackJack $blackJack
     * @param string $action
     * @return BlackJack
     */
    public function play(BlackJack $blackJack, string $action): BlackJack
    {
        $split = false;

        switch ($action) {
            case "hit":
                $blackJack = $this->hit($blackJack, $blackJack->getActualPlayer());
                $split = true;
                $blackJack->getActualPlayer()->setChoice("classic");
                break;
            case "stand":
                if ($this->countPoint($blackJack->getActualPlayer()) == 21) {
                    $blackJack->getActualPlayer()->setChoice("blackjack");
                } else {
                    $blackJack->getActualPlayer()->setChoice("classic");
                }
                break;
            case "double":
                $blackJack = $this->hit($blackJack, $blackJack->getActualPlayer());
                $rank = $this->rankRepository->findOneBy(array("gamemod" => $blackJack->getParty()->getGamemod(), "user" => $blackJack->getActualPlayer()->getUser()));
                $this->rankRepository->save($rank->setMmr($rank->getMmr() - $blackJack->getParty()->getBet()));
                $blackJack->getActualPlayer()->setChoice("double");
                break;
            case "split":
                $rank = $this->rankRepository->findOneBy(array("gamemod" => $blackJack->getParty()->getGamemod(), "user" => $blackJack->getActualPlayer()->getUser()));
                $this->rankRepository->save($rank->setMmr($rank->getMmr() - $blackJack->getParty()->getBet()));
                $blackJack->getActualPlayer()->setChoice("split");
                $players = $blackJack->getPlayers();
                $new_player = new Player();
                $new_player->setUser(clone $blackJack->getActualPlayer()->getUser())->setChoice($blackJack->getActualPlayer()->getChoice())->addHand(clone $blackJack->getActualPlayer()->getHand()[1]);
                array_splice($players, array_search($blackJack->getActualPlayer(), $blackJack->getPlayers()) + 1, 0, [$new_player]);
                $blackJack->setPlayers($players);
                $blackJack->setNextPlayer($blackJack->getPlayers()[array_search($blackJack->getActualPlayer(), $blackJack->getPlayers()) + 1]);
                $blackJack->getActualPlayer()->getHand()->removeElement($blackJack->getActualPlayer()->getHand()[1]);
                $deck = $blackJack->getDeck();
                $blackJack->getActualPlayer()->addHand($this->cardkRepo->pickCard($deck));
                $blackJack->getNextPlayer()->addHand($this->cardkRepo->pickCard($deck));
                $split = true;
                break;
        }

        if ($split == false) {
            $blackJack->setActualPlayer($blackJack->getNextPlayer());
            if (get_class($blackJack->getNextPlayer()) != "App\Entity\Croupier") {
                $blackJack->setNextPlayer($blackJack->getPlayers()[array_search($blackJack->getNextPlayer(), $blackJack->getPlayers()) + 1]);
            } else {
                $blackJack->setNextPlayer(null);
            }
        }
        return $blackJack;
    }

    /**
     * function for hit card ans add in hand of player
     *
     * @param BlackJack $blackJack
     * @param Player $player
     * @return BlackJack
     */
    private function hit(BlackJack $blackJack, Player $player): BlackJack
    {
        $deck = $blackJack->getDeck();
        $player->addHand($this->cardkRepo->pickCard($deck));
        return $blackJack;
    }

    /**
     * function that count point of his card
     *
     * @param Player $player
     * @return integer
     */
    private function countPoint(Player $player): int
    {
        $point = 0;
        $ass = 0;
        foreach ($player->getHand() as $card) {
            if ($card->getValue() == 1) {
                $ass++;
            } else if ($card->getValue() >= 10) {
                $point += 10;
            } else {
                $point += $card->getValue();
            }
        }
        if ($ass != 0) {
            for ($i = 0; $i <= $ass; $i++) {
                if ($point > 10) {
                    $point += 1;
                } else {
                    $point += 11;
                }
            }
        }

        return $point;
    }



    //    /**
    //     * @return Party[] Returns an array of Party objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Party
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
