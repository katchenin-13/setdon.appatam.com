<?php

namespace App\Repository;

use App\Entity\Missionrapport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Missionrapport>
 *
 * @method Missionrapport|null find($id, $lockMode = null, $lockVersion = null)
 * @method Missionrapport|null findOneBy(array $criteria, array $orderBy = null)
 * @method Missionrapport[]    findAll()
 * @method Missionrapport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionrapportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Missionrapport::class);
    }

    public function save(Missionrapport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Missionrapport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countItems()
    {
        $qb = $this->createQueryBuilder('m');
        return $qb
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Missionrapport[] Returns an array of Missionrapport objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Missionrapport
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
