<?php

namespace App\Repository;

use App\Entity\Communaute;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Communaute>
 *
 * @method Communaute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Communaute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Communaute[]    findAll()
 * @method Communaute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunauteRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Communaute::class);
        $this->em = $em;
    }

    public function save(Communaute $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Communaute $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // public function getAnneeRangeContrat($typeContrat)
    // {
    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     $tableEmploye = $this->getTableName(Employe::class, $em);
    //     $tableGenre = $this->getTableName(Genre::class, $em);
    //     $tableNMaitrise = $this->getTableName(NiveauMaitrise::class, $em);
    //     $tableHierarchie = $this->getTableName(NiveauHierarchique::class, $em);
    //     $tableUnite = $this->getTableName(UniteEmploye::class, $em);
    //     $tableContrat = $this->getTableName(Contrat::class, $em);
    //     $sql = <<<SQL
    //     SELECT MIN(YEAR(date_debut)) AS min_year, MAX(YEAR(date_debut)) AS max_year
    //     FROM {$tableContrat}
    //     WHERE type_contrat_id = :type_contrat
    //     SQL;
    //     $params['type_contrat'] = $typeContrat;


    //     $stmt = $connection->executeQuery($sql, $params);
    //     return $stmt->fetchAssociative();
    // }


    // public function getDataTypeContrat($typeContrat)
    // {
    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     // $tableEmploye = $this->getTableName(Employe::class, $em);
    //     // $tableGenre = $this->getTableName(Genre::class, $em);
    //     // $tableNMaitrise = $this->getTableName(NiveauMaitrise::class, $em);
    //     // $tableHierarchie = $this->getTableName(NiveauHierarchique::class, $em);
    //     // $tableUnite = $this->getTableName(UniteEmploye::class, $em);
    //     $tableContrat = $this->getTableName(Contrat::class, $em);
    //     $sql = <<<SQL
    //     SELECT COUNT(*) AS _total, YEAR(date_debut)
    //     FROM {$tableContrat}
    //     WHERE type_contrat_id = :type_contrat
    //     GROUP BY YEAR(date_debut)
    //     ORDER BY YEAR(date_debut) ASC
    //     SQL;
    //     $params['type_contrat'] = $typeContrat;
    //     $stmt = $connection->executeQuery($sql, $params);
    //     return $stmt->fetchAllAssociative();
    // }
   
//    /**
//     * @return Communaute[] Returns an array of Communaute objects
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

//    public function findOneBySomeField($value): ?Communaute
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
