<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Audience;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCalendrier()
    {

        $query1 = $this->manager->getRepository(Demande::class)->createQueryBuilder('d')
            ->Where("CURRENT_DATE() <= d.daterencontre ")
            ->andWhere('d.etat = :status')
            ->setParameter('status', 'demande_valider')
            ->getQuery();


        try {
            $result1 = $query1->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $result1 = [];
        }

        $query2 = $this->manager->getRepository(Audience::class)->createQueryBuilder('a')
            ->Where("CURRENT_DATE() <= a.daterencontre ")
            ->andWhere('a.etat = :status')
            ->setParameter('status', 'audience_valider')
            ->getQuery();

        try {
            $result2 = $query2->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $result2 = [];
        }

        return array_merge($result1, $result2);
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
