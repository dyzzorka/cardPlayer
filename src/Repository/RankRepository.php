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

    public function updateOrCreateRank(GameMod $gameMod, User $user, int $mmr): Rank
    {
        $rank = $this->findOneBy(array("gamemod" => $gameMod, "user" => $user));
        if ($rank === null) {
            $rank = new Rank();
            $rank->setUser($user)->setGamemod($gameMod)->setMmr($mmr)->setStatus(true);
            $this->save($rank, true);
        } else {
            $actualMmr = $rank->getMmr();
            $rank->setMmr($actualMmr += $mmr)->setStatus(true);
            $this->save($rank, true);
        }
        return $rank;
    }

    public function getMmr(GameMod $gameMod, User $user): int
    {
        $result =  $this->findOneBy(array("gamemod" => $gameMod, "user" => $user))->getMmr() ?? $result = -1;
        return $result;
    }

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
