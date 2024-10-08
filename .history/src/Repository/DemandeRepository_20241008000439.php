<?php

namespace App\Repository;

use App\Entity\Demande;
use App\Entity\Localite;
use App\Entity\Categorie;
use App\Form\DemandeType;
use App\Entity\Communaute;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Demande>
 *
 * @method Demande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demande[]    findAll()
 * @method Demande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Demande::class);
        $this->em = $em;
    }
   
    public function save(Demande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Demande $entity, bool $flush = false): void
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

    public function getDemandeValider()
    {
        return $this->createQueryBuilder('d')
            ->Where("CURRENT_DATE() <= d.daterencontre ")
            ->andWhere('d.etat = :status')
            ->setParameter('status', 'demande_valider')
            ->getQuery()
            ->getResult();
    }


    public function getDateDebut()
    {
        return $this->createQueryBuilder('d')
            ->select('YEAR(d.daterencontre) as annee')
            ->groupBy('annee')
            ->getQuery()
            ->getResult();
    }

    public function getDemandeParMoisCommunauteTableauDemande($date)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableDemande = $this->getTableName(Demande::class,$em);



        if ($date != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%Y') as mois, d.etat AS etat
            FROM {$tableDemande} d
            WHERE YEAR(daterencontre) in (:date) 
            GROUP BY mois,etat
        SQL;
        }


        $params['date'] = $date;
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }
    
    

    public function getDemandeParMoisEtCommunaute($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);



        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(daterencontre) in (:date)  AND D.communaute_id =:communaute
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois,d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE a.communaute_id =:communauteId
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois,d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            WHERE YEAR(daterencontre) in (:date)
            GROUP BY mois,etat
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getDemandeParMoisEtCategorie($date, $categorieId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableCatgorie = $this->getTableName(Categorie::class, $em);



        if ($date != null && $categorieId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(daterencontre) in (:date)  AND c.id =:categorie
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $categorieId != null) {
            $sql = <<<SQL
             SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(daterencontre) in (:date)  AND c.id =:categorie
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $categorieId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(daterencontre) in (:date)  AND c.id =:categorie
            GROUP BY mois,etat
            SQL;
        }


        $params['date'] = $date;
        $params['categorie'] = $categorieId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }
    public function getDemandeParMoisEtLocalite($date, $localiteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableLocalite = $this->getTableName(Localite::class, $em);



        if ($date != null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(daterencontre) in (:date)  AND l.id =:localite
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(daterencontre) in (:date)  AND l.id =:localite
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $localiteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, d.etat AS etat
            FROM {$tableDemande} d
            JOIN {$tableCommunaute} com ON com.id = d.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(daterencontre) in (:date)  AND l.id =:localite
            GROUP BY mois,etat
            SQL;
        }


        $params['date'] = $date;
        $params['localite'] = $localiteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function findValidDemands()
    {
        $query = $this->createQueryBuilder('d')
            ->where("CURRENT_DATE() <= d.daterencontre")
            ->andWhere('d.etat = :status')
            ->setParameter('status', 'demande_valider')
            ->getQuery();

        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return [];
        }
    }



//    /**
//     * @return Demande[] Returns an array of Demande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'DSC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Demande
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
