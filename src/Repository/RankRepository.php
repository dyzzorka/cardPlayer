<?php

namespace App\Repository;

use App\Entity\GameMod;
use App\Entity\Party;
use App\Entity\Rank;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rank>
 *
 * @method Rank|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rank|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rank[]    findAll()
 * @method Rank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rank::class);
    }

    public function save(Rank $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rank $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * function that retrieves the rank with the user and the gamemod
     *
     * @param GameMod $gameMod
     * @param User $user
     * @return integer
     */
    public function getMmr(GameMod $gameMod, User $user): int
    {
        if ($this->findOneBy(array("gamemod" => $gameMod, "user" => $user)) == null) {
            $result = -1;
        } else {
            $result = $this->findOneBy(array("gamemod" => $gameMod, "user" => $user))->getMmr();
        }
        return $result;
    }

    /**
     * function that charges players (in MMR)
     *
     * @param Party $party
     * @return void
     */
    public function payMmr(Party $party)
    {
        foreach ($party->getUsers() as $user) {
            $rank = $this->findOneBy(array("gamemod" => $party->getGamemod(), "user" => $user));
            $rank->setMmr($rank->getMmr() - $party->getBet());
            $this->save($rank, true);
        }
    }

    //    /**
    //     * @return Rank[] Returns an array of Rank objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rank
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
