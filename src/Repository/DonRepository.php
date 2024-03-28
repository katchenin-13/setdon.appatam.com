<?php

namespace App\Repository;

use App\Entity\Communaute;
use App\Entity\Don;
use App\Entity\Typedon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Don>
 *
 * @method Don|null find($id, $lockMode = null, $lockVersion = null)
 * @method Don|null findOneBy(array $criteria, array $orderBy = null)
 * @method Don[]    findAll()
 * @method Don[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Don::class);
        $this->em = $em;
    }
    public function save(Don $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Don $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function countItems()
    {
        $qb = $this->createQueryBuilder('t');
        return $qb
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // public function getDateDebut()
    // {
    //     return $this->createQueryBuilder('d')
    //         ->select('YEAR(d.dateremise) as annee')
    //         ->groupBy('annee')
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function getDonParMoisEtCommunaute($date, $communauteId)
    // {

    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     $tableCommunaute = $this->getTableName(Communaute::class, $em);
    //     $tableTypedon = $this->getTableName(Typedon::class, $em);
    //     $tableDon = $this->getTableName(Don::class, $em);




    //     if ($date != null && $communauteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, t.code AS typedon
    //         FROM {$tableDon} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableTypedon} t ON t.id = d.typedon_id
    //         WHERE YEAR(dateremise) in (:date)  AND a.communaute_id =:communaute 
    //         GROUP BY mois, typedon
    //     SQL;
    //     } elseif ($date == null && $communauteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois,t.code AS typedon
    //         FROM {$tableDon} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableTypedon} t ON t.id = d.typedon_id
    //         WHERE a.communaute_id =:communauteId
    //         GROUP BY mois, typedon
    //         SQL;
    //     } elseif ($date != null && $communauteId == null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois,t.code AS typedon
    //         FROM {$tableDon} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableTypedon} t ON t.id = d.typedon_id
    //         WHERE YEAR(dateremise) in (:date)
    //         GROUP BY mois, typedon
    //         SQL;
    //     }


    //     $params['date'] = $date;
    //     $params['communaute'] = $communauteId;


    //     $stmt = $connection->executeQuery($sql, $params);

    //     return $stmt->fetchAllAssociative();
    // }

    // public function getDemandeParMoisEtCategorie($date, $categorieId)
    // {

    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     $tableCommunaute = $this->getTableName(Typedon::class, $em);
    //     $tableDemande = $this->getTableName(Don::class, $em);
    //     $tableCatgorie = $this->getTableName(Categorie::class, $em);



    //     if ($date != null && $categorieId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableCatgorie} c ON c.id = com.categorie_id
    //         WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
    //         GROUP BY mois, etat
    //     SQL;
    //     } elseif ($date == null && $categorieId != null) {
    //         $sql = <<<SQL
    //          SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableCatgorie} c ON c.id = com.categorie_id
    //         WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
    //         GROUP BY mois,etat
    //         SQL;
    //     } elseif ($date != null && $categorieId == null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableCatgorie} c ON c.id = com.categorie_id
    //         WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
    //         GROUP BY mois,etat
    //         SQL;
    //     }


    //     $params['date'] = $date;
    //     $params['categorie'] = $categorieId;


    //     $stmt = $connection->executeQuery($sql, $params);

    //     return $stmt->fetchAllAssociative();
    // }
    // public function getDemandeParMoisEtLocalite($date, $localiteId)
    // {

    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     $tableCommunaute = $this->getTableName(Communaute::class, $em);
    //     $tableDemande = $this->getTableName(Demande::class, $em);
    //     $tableLocalite = $this->getTableName(Localite::class, $em);



    //     if ($date != null && $localiteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableLocalite} l ON l.id = com.localite_id
    //         WHERE YEAR(dateremise) in (:date)  AND l.id =:localite
    //         GROUP BY mois, etat
    //     SQL;
    //     } elseif ($date == null && $localiteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableLocalite} l ON l.id = com.localite_id
    //         WHERE YEAR(dateremise) in (:date)  AND l.id =:localite
    //         GROUP BY mois,etat
    //         SQL;
    //     } elseif ($date != null && $localiteId == null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(dateremise,'%M') as mois, d.etat AS etat
    //         FROM {$tableDemande} d
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         JOIN {$tableLocalite} l ON l.id = com.localite_id
    //         WHERE YEAR(dateremise) in (:date)  AND l.id =:localite
    //         GROUP BY mois,etat
    //         SQL;
    //     }


    //     $params['date'] = $date;
    //     $params['localite'] = $localiteId;


    //     $stmt = $connection->executeQuery($sql, $params);

    //     return $stmt->fetchAllAssociative();
    // }






//    /**
//     * @return Don[] Returns an array of Don objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Don
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
