<?php

namespace App\Repository;

use Mission;
use App\Entity\Missionrapport;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Mission>
 *
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

    public function save(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listFieldByGroup($don, $type): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.don', 'd')
            ->andWhere('f.typedonsfiel= :type')
            ->andWhere('d.id= :don')
            ->setParameters(array('don' => $don, 'type' => $type))
            ->getQuery()
            ->getResult();
    }

    public function getDateDebut()
    {
        return $this->createQueryBuilder('m')
            ->select('YEAR(m.CreatedAt) as annee')
            ->groupBy('annee')
            ->getQuery()
            ->getResult();
    }

    public function getDonParMoisCommunauteTableauDon($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableMissionrappor = $this->getTableName(Missionrapport::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);


        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
            FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE YEAR(m.created_at) in (:date)  AND m.communaute_id =:communaute
            GROUP BY mois,typedonsfiel
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
            FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE  m.communaute_id =:communaute
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
             SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
             FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE YEAR(f.created_at) in (:date) 
            GROUP BY mois,etat
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
        $tableMissionrappor = $this->getTableName(Missionrapport::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);





        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
           SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
            FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE YEAR(m.created_at) in (:date)  AND m.communaute_id =:communaute
            GROUP BY mois,typedonsfiel
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
            FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE YEAR(m.created_at) in (:date)  AND m.communaute_id =:communaute
            GROUP BY mois,typedonsfiel
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(m.created_at,'%M') as mois,  m.etat AS etat
            FROM {$tableMissionrappor} m
            JOIN {$tableCommunaute} com ON com.id = m.communaute_id
            WHERE YEAR(m.created_at) in (:date)  
            
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


//    /**
//     * @return Mission[] Returns an array of Mission objects
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

//    public function findOneBySomeField($value): ?Mission
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
