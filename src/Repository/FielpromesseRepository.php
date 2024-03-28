<?php

namespace App\Repository;

use App\Entity\Don;
use App\Entity\Typedon;
use App\Entity\Localite;
use App\Entity\Promesse;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Entity\Fielpromesse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Fielpromesse>
 *
 * @method Fielpromesse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fielpromesse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fielpromesse[]    findAll()
 * @method Fielpromesse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FielpromesseRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Fielpromesse::class);
        $this->em = $em;
    }
    public function save(Fielpromesse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fielpromesse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countItems()
    {
        $qb = $this->createQueryBuilder('f');
        return $qb
            ->select('count(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function listFieldByGroup($promesse, $type): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.promesse', 'd')
            ->andWhere('f.typepromesse = :type')
            ->andWhere('d.id= :promesse')
            ->setParameters(array('promesse' => $promesse, 'type' => $type))
            ->getQuery()
            ->getResult();
    }

   


    public function getDateDebut()
    {
        return $this->createQueryBuilder('f')
            ->select('YEAR(f.CreatedAt) as annee')
            ->groupBy('annee')
            ->getQuery()
            ->getResult();
    }

    // public function getPromesseParMoisCommunauteTableauDons($date, $communauteId)
    // {

    //     $em = $this->getEntityManager();
    //     $connection = $em->getConnection();
    //     $tableFielpromesse = $this->getTableName(Fielpromesse::class, $em);
    //     $tablePromesse = $this->getTableName(Promesse::class, $em);
    //     $tableCommunaute = $this->getTableName(Communaute::class, $em);


    //     if ($date != null && $communauteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.etat AS etat
    //          FROM {$tableFielpromesse} f
    //         JOIN {$tablePromesse} d ON d.id = f.promesse_id
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute
    //         GROUP BY mois,etat
    //     SQL;
    //     } elseif ($date == null && $communauteId != null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.etat AS etat
    //         FROM {$tableFielpromesse} f
    //         JOIN {$tablePromesse} d ON d.id = f.promesse_id
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         WHERE   d.communaute_id =:communaute
    //         GROUP BY mois,etat
    //         SQL;
    //     } elseif ($date != null && $communauteId == null) {
    //         $sql = <<<SQL
    //         SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.etat AS etat
    //         FROM {$tableFielpromesse} f
    //         JOIN {$tablePromesse} d ON d.id = f.promesse_id
    //         JOIN {$tableCommunaute} com ON com.id = d.communaute_id
    //         WHERE YEAR(f.created_at) in (:date) 
    //         GROUP BY mois,etat
    //         SQL;
    //     }


    //     $params['date'] = $date;
    //     $params['communaute'] = $communauteId;


    //     $stmt = $connection->executeQuery($sql, $params);

    //     return $stmt->fetchAllAssociative();
    // }

    public function getPromesseParMoisCommunauteTableauPromesse($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFielpromesse = $this->getTableName(Fielpromesse::class, $em);
        $tablePromesse = $this->getTableName(Promesse::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);


        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typepromesse AS typepromesse,f.etat AS etat
             FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute
            GROUP BY mois,typepromesse,etat
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typepromesse AS typepromesse,f.etat AS etat
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE  d.communaute_id =:communaute
            GROUP BY mois,typepromesse
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typepromesse AS typepromesse,f.etat AS etat
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date) 
            GROUP BY mois,typepromesse,etat
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getPromesseParMoisEtCommunaute($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFielpromesse = $this->getTableName(Fielpromesse::class, $em);
        $tablePromesse = $this->getTableName(Promesse::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);

        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute 
            GROUP BY mois, typepromesse
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute 
            GROUP BY mois, typepromesse
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute 
            GROUP BY mois, typepromesse
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getPromesseParMoisEtCategorie($date, $categorieId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFielpromesse = $this->getTableName(Fielpromesse::class, $em);
        $tablePromesse = $this->getTableName(Promesse::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableCatgorie = $this->getTableName(Categorie::class, $em);



        if ($date != null && $categorieId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
            GROUP BY mois, etat,typepromesse
        SQL;
        } elseif ($date == null && $categorieId != null) {
            $sql = <<<SQL
             SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
            GROUP BY mois,etat,typepromesse
            SQL;
        } elseif ($date != null && $categorieId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(dateremise) in (:date)  AND c.id =:categorie
            GROUP BY mois,etat,typepromesse
            SQL;
        }


        $params['date'] = $date;
        $params['categorie'] = $categorieId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }
    public function getPromesseParMoisEtLocalite($date, $localiteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFielpromesse = $this->getTableName(Fielpromesse::class, $em);
        $tablePromesse = $this->getTableName(Promesse::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);

        $tableLocalite = $this->getTableName(Localite::class, $em);



        if ($date != null && $localiteId != null) {
            $sql = <<<SQL
           SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(dateremise) in (:date)  AND l.id =:localite
            GROUP BY mois, etat,typepromesse
        SQL;
        } elseif ($date == null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE l.id =:localite
            GROUP BY mois,etat,typepromesse
            SQL;
        } elseif ($date != null && $localiteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typepromesse AS typepromesse
            FROM {$tableFielpromesse} f
            JOIN {$tablePromesse} d ON d.id = f.promesse_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(dateremise) in (:date)
            GROUP BY mois,etat,typepromesse
            SQL;
        }


        $params['date'] = $date;
        $params['localite'] = $localiteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }


//    /**
//     * @return Fielpromesse[] Returns an array of Fielpromesse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fielpromesse
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
