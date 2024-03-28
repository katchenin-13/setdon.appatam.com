<?php

namespace App\Repository;

use App\Entity\Don;
use App\Entity\Fieldon;
use App\Entity\Typedon;
use App\Entity\Localite;
use App\Entity\Categorie;
use App\Entity\Communaute;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Fieldon>
 *
 * @method Fieldon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fieldon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fieldon[]    findAll()
 * @method Fieldon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FieldonRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Fieldon::class);
        $this->em = $em;
    }
    public function save(Fieldon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fieldon $entity, bool $flush = false): void
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

    public function listFieldByGroup($don,$type): array
   {
       return $this->createQueryBuilder('f')
            ->innerJoin('f.don','d')
            ->andWhere('f.typedonsfiel= :type')
            ->andWhere('d.id= :don')
            ->setParameters(array('don'=> $don, 'type'=>$type))
           ->getQuery()
           ->getResult()
       ;
   }

    public function getDateDebut()
    {
            return $this->createQueryBuilder('f')
                ->select('YEAR(f.CreatedAt) as annee')
                ->groupBy('annee')
                ->getQuery()
                ->getResult();
    }

    public function getDonParMoisCommunauteTableauDon($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFieldon = $this->getTableName(Fieldon::class, $em);
        $tableDon = $this->getTableName(Don::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);


        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typedonsfiel AS typedonsfiel
             FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute
            GROUP BY mois,typedonsfiel
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE  d.communaute_id =:communaute
            GROUP BY mois,typedonsfiel
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%Y') as mois,  f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date) 
            GROUP BY mois,typedonsfiel
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getDonParMoisEtCommunaute($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableFieldon = $this->getTableName(Fieldon::class, $em);
        $tableDon = $this->getTableName(Don::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
       




        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois, f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)  AND d.communaute_id =:communaute 
            GROUP BY mois,typedonsfiel
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE d.communaute_id =:communaute 
            GROUP BY mois,typedonsfiel
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(f.created_at) in (:date)
            GROUP BY mois,typedonsfiel
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getDonParMoisEtCategorie($date, $categorieId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        $tableFieldon = $this->getTableName(Fieldon::class, $em);
        $tableDon = $this->getTableName(Don::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableCatgorie = $this->getTableName(Categorie::class, $em);



        if ($date != null && $categorieId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(f.created_at) in (:date)  AND c.id =:categorie
            GROUP BY mois, typedonsfiel
        SQL;
        } elseif ($date == null && $categorieId != null) {
            $sql = <<<SQL
             SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE  c.id =:categorie
            GROUP BY mois,typedonsfiel
            SQL;
        } elseif ($date != null && $categorieId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(f.created_at) in (:date)  
            GROUP BY mois,typedonsfiel
            SQL;
        }


        $params['date'] = $date;
        $params['categorie'] = $categorieId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }
    public function getDonParMoisEtLocalite($date, $localiteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
     
        $tableFieldon = $this->getTableName(Fieldon::class, $em);
        $tableDon = $this->getTableName(Don::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableLocalite = $this->getTableName(Localite::class, $em);



        if ($date != null && $localiteId != null) {
            $sql = <<<SQL
           SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(f.created_at) in (:date)  AND l.id =:localite
            GROUP BY mois, typedonsfiel
        SQL;
        } elseif ($date == null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE l.id =:localite
            GROUP BY mois,typedonsfiel
            SQL;
        } elseif ($date != null && $localiteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(f.created_at,'%M') as mois,f.typedonsfiel AS typedonsfiel
            FROM {$tableFieldon} f
            JOIN {$tableDon} d ON d.id = f.don_id
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(f.created_at) in (:date)  
            GROUP BY mois,typedonsfiel
            SQL;
        }


        $params['date'] = $date;
        $params['localite'] = $localiteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    // public function  afficheModule()
    // {
    //     return $this->createQueryBuilder('m')
    //         ->select('md.id', 'md.titre', 'md.ordre')
    //         //            ->where('m.groupeUser = : val')
    //         ->innerJoin('m.typedon', 'md')
    //         ->andWhere('gu.id = :val')
    //         ->groupBy('md.id')
    //         ->orderBy('md.ordre', 'ASC')
    //         /*  ->setMaxResults(10)*/
    //         ->getQuery()
    //         ->getResult();
    // }
//    /**
//     * @return Fieldon[] Returns an array of Fieldon objects
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

//    public function findOneBySomeField($value): ?Fieldon
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
