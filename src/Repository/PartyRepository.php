<?php

namespace App\Repository;

use App\Entity\BlackJack;
use App\Entity\Party;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
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

    public function __construct(ManagerRegistry $registry, CardRepository $cardkRepo, RankRepository $rankRepository)
    {
        $this->cardkRepo = $cardkRepo;
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

    public function playCroupiers(BlackJack $blackJack)
    {
        dd("end game");
        return $blackJack;
    }

    public function play(BlackJack $blackJack, string $action): BlackJack
    {
        $split = false;

        switch ($action) {
            case "hit":
                $blackJack = $this->hit($blackJack);
                $split = true;
                break;
            case "stand":
                break;
            case "double":
                $blackJack = $this->hit($blackJack);
                $rank = $this->rankRepository->findOneBy(array("gamemod" => $blackJack->getParty()->getGamemod(), "user" => $blackJack->getActualPlayer()->getUser()));
                $this->rankRepository->save($rank->setMmr($rank->getMmr() - $blackJack->getParty()->getBet()));
                $blackJack->getActualPlayer()->setChoice("double");
                break;
            case "split":
                
                $rank = $this->rankRepository->findOneBy(array("gamemod" => $blackJack->getParty()->getGamemod(), "user" => $blackJack->getActualPlayer()->getUser()));
                $this->rankRepository->save($rank->setMmr($rank->getMmr() - $blackJack->getParty()->getBet()));
                $blackJack->getActualPlayer()->setChoice("double");

                $players = $blackJack->getPlayers();
                array_splice($players, array_search($blackJack->getActualPlayer(), $blackJack->getPlayers()) + 1, 0, $blackJack->getActualPlayer());/* -> pb y comprend que c'est une array */
                $blackJack->setPlayers($players);

                dd($blackJack);
                $blackJack->setNextPlayer($blackJack->getPlayers()[array_search($blackJack->getActualPlayer(), $blackJack->getPlayers()) + 1]);
                unset($blackJack->getActualPlayer()[1]);
                unset($blackJack->getNextPlayer()[0]);
                $blackJack = $this->hitAfterSplit($blackJack, $blackJack->getActualPlayer());
                $blackJack = $this->hitAfterSplit($blackJack, $blackJack->getNextPlayer());
                $split = true;
                break;
            case "surrend":
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

    private function hit(BlackJack $blackJack): BlackJack
    {
        $deck = $blackJack->getDeck();
        $blackJack->getActualPlayer()->addHand($this->cardkRepo->pickCard($deck));
        return $blackJack;
    }

    private function hitAfterSplit(BlackJack $blackJack, Player $player): BlackJack
    {
        $deck = $blackJack->getDeck();
        $player->addHand($this->cardkRepo->pickCard($deck));
        return $blackJack;
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
