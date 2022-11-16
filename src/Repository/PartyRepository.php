<?php

namespace App\Repository;

use App\Entity\BlackJack;
use App\Entity\Party;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    public function __construct(ManagerRegistry $registry, CardRepository $cardkRepo)
    {
        $this->cardkRepo = $cardkRepo;
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

    public function play(BlackJack $blackJack, string $action): BlackJack
    {

        switch ($action) {
            case "hit":
                $blackJack = $this->hit($blackJack);
                break;
            case "stand":
                break;
            case "double":
                echo "i égal 0";
                break;
            case "split":
                echo "i égal 0";
                break;
            case "surrend":
                $blackJack->getParty()->removeUser($blackJack->getActualPlayer()->getUser());
                $blackJack->removePlayers($blackJack->getActualPlayer());
                break;
        }

        $blackJack->setActualPlayer($blackJack->getNextPlayer());
        if (get_class($blackJack->getNextPlayer()) != "App\Entity\Croupier") {
            $blackJack->setNextPlayer($blackJack->getPlayers()[array_search($blackJack->getNextPlayer(), $blackJack->getPlayers()) + 1]);
        } else {
            $blackJack->setNextPlayer(null);
        }


        return $blackJack;
    }

    private function hit(BlackJack $blackJack): BlackJack
    {
        $deck = $blackJack->getDeck();
        $blackJack->getActualPlayer()->addHand($this->cardkRepo->pickCard($deck));
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
