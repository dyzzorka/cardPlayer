<?php

namespace App\Repository;

use App\Entity\GameMod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameMod>
 *
 * @method GameMod|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameMod|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameMod[]    findAll()
 * @method GameMod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameModRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameMod::class);
    }

    public function save(GameMod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GameMod $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GameMod[] Returns an array of GameMod objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findOneBySomeField($value): ?GameMod
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
