<?php

namespace App\Repository;

use App\Entity\BlackJack;
use App\Entity\Card;
use App\Entity\Croupier;
use App\Entity\Party;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function save(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Card $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function pickCard(array &$cards): Card {
        $mycard = $cards[0];
        array_shift($cards);
        return $mycard;
    }

    public function doDeck(Party $party): array
    {
        $cardList = $party->getGamemod()->getCards()->toArray();
        $deck = array();
        for ($i = 0; $i < 6; $i++) {
            $deck = array_merge($deck, $cardList);
        }
        shuffle($deck);
        return $deck;
    }

    public function distribCards(BlackJack & $blackJack) {

        $deck = $blackJack->getDeck();
        for ($i = 0; $i <2; $i++) {
            foreach($blackJack->getPlayers() as $player) {
                if (get_class($player) == "App\Entity\Croupier" && $i == 1) {
                    $player->setBackcard($this->pickCard($deck));
                    $player->addHand($this->find(53));
                } else {
                    $player->addHand($this->pickCard($deck));
                }
                $blackJack->setDeck($deck);
            }
        }

    }

    //    /**
    //     * @return Card[] Returns an array of Card objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Card
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
