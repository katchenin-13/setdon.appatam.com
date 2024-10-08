<?php

namespace App\Repository;

use App\Entity\Audience;
use App\Entity\Categorie;
use App\Entity\Communaute;
use App\Entity\Localite;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator  as Paginator;

/**
 * @extends ServiceEntityRepository<Audience>
 *
 * @method Audience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Audience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Audience[]    findAll()
 * @method Audience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudienceRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    private  $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Audience::class);
        $this->em = $em;
    }


    public function save(Audience $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Audience $entity, bool $flush = false): void
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
   
    public function getDateDebut()
    {
        return $this->createQueryBuilder('a')
            ->select('YEAR(a.daterencontre) as annee')
            ->groupBy('annee')
            ->getQuery()
            ->getResult();
    }
    public function getaudience()
    {
        return $this->createQueryBuilder('a')
            ->select('a.id,a.motif')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithPagination(int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $paginator->getIterator()->getArrayCopy();
    }

    public function findAllWithPagination1(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    } 
 
    public function getAudienceValider()
    {
        return $this->createQueryBuilder('a')
            ->Where("CURRENT_DATE() <= a.daterencontre ")
            ->andWhere('a.etat = :status')
            ->setParameter('status', 'audience_valider')
            ->getQuery()
            ->getResult();
    }

    //liste des audience initialiser
    public function findByAudienceInitial(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.etat = :status')
            ->setParameter('status', 'audience_initie')
            ->getQuery()
            ->getResult();
    }

    //liste des audiences valider
    public function findByAudienceRejeter(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.etat = :status')
            ->setParameter('status', 'audience_rejeter')
            ->getQuery()
            ->getResult();
    }

    //liste des audience rejeter
    public function findByAudienceValider(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.etat = :status')
            ->setParameter('status', ' audience_valider')
            ->getQuery()
            ->getResult();
    }


    public function getAudienceParMoisEtCommunauteTableauAudience($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);



        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%Y') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)  AND a.communaute_id =:communaute
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%Y') as mois,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE a.communaute_id =:communauteId
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%Y') as mois,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)
            GROUP BY mois,etat
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getAudienceParEtatPieEtCommunauteTableauAudience($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);



        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)  AND a.communaute_id =:communaute
            GROUP BY mois
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE a.communaute_id =:communauteId
            GROUP BY mois
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)
            GROUP BY mois
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getAudienceEtCommunauteTableauAudience($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);



        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)  AND a.communaute_id =:communaute
            GROUP BY  etat
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE a.communaute_id =:communauteId
            GROUP BY etat
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)
            GROUP BY etat
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getAudienceParMoisEtCommunaute($date, $communauteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);



        if ($date != null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)  AND a.communaute_id =:communaute
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $communauteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE a.communaute_id =:communauteId
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $communauteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            WHERE YEAR(daterencontre) in (:date)
            GROUP BY mois,etat
            SQL;
        }


        $params['date'] = $date;
        $params['communaute'] = $communauteId;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getAudienceParMoisEtCategorie($date, $categorieId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);
        $tableCatgorie = $this->getTableName(Categorie::class, $em);



        if ($date != null && $categorieId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(daterencontre) in (:date)  AND c.id =:categorie
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $categorieId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_idte
            JOIN {$tableCatgorie} c ON c.id = com.categorie_id
            WHERE YEAR(daterencontre) in (:date)  AND c.id =:categorie
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $categorieId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
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
    public function getAudienceParMoisEtLocalite($date, $localiteId)
    {

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);
        $tableLocalite = $this->getTableName(Localite::class, $em);



        if ($date != null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(daterencontre) in (:date)  AND l.id =:localite
            GROUP BY mois, etat
        SQL;
        } elseif ($date == null && $localiteId != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois, a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
            JOIN {$tableLocalite} l ON l.id = com.localite_id
            WHERE YEAR(daterencontre) in (:date)  AND l.id =:localite
            GROUP BY mois,etat
            SQL;
        } elseif ($date != null && $localiteId == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(daterencontre,'%M') as mois,a.etat AS etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
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


    public function getAudienceParAnneeEtCommunaute1($dateDebut, $communauteId)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);

        $sql = <<<SQL
            SELECT COUNT(*) AS _total, a.etat as etat
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
           
           SQL;

        $params['dateDebut'] = $dateDebut;
        //  $params['dateFin'] = $dateFin;
        $params['communaute'] = $communauteId;
        //$params['etat'] = $etat;
        $sql .= ' WHERE YEAR(a.daterencontre) =:dateDebut   AND com.id =:communaute';
        $sql .= ' GROUP BY etat';
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function findValidAudiences()
    {
        $query = $this->createQueryBuilder('a')
            ->where("CURRENT_DATE() <= a.daterencontre")
            ->andWhere('a.etat = :status')
            ->setParameter('status', 'audience_valider')
            ->getQuery();

        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return [];
        }
    }


    // public function getAudienceParAnneeEtCommunaute($dateDebut,$dateFin,$communauteId)
    // {
    //         $em = $this->getEntityManager();
    //         $connection = $em->getConnection();
    //         $tableCommunaute = $this->getTableName(Communaute::class, $em);
    //         $tableAudience = $this->getTableName(Audience::class, $em);

    //             $sql = <<<SQL
    //             SELECT COUNT(*) AS _total, a.etat as etat,YEAR(a.daterencontre) as _annee
    //             FROM {$tableAudience} a
    //             JOIN {$tableCommunaute} com ON com.id = a.communaute_id

    //            SQL; 

    //         $params['dateDebut'] = $dateDebut;
    //         $params['dateFin'] = $dateFin;
    //         $params['communaute'] = $communauteId;
    //         //$params['etat'] = $etat;
    //  $sql .= ' WHERE YEAR(a.daterencontre) between :dateDebut and :dateFin and  a.etat in ("audience_rejeter","audience_valider") AND com.id=:communaute AND ';
    //         $sql .= ' GROUP BY etat,_annee';
    // $stmt = $connection->executeQuery($sql, $params);
    // return $stmt->fetchAllAssociative();
    // }

    public function getAnneAudienceParAnneeEtCommunaute($dateDebut, $dateFin, $communauteId)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);

        $sql = <<<SQL
            SELECT YEAR(a.daterencontre) as _annee
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
           
           SQL;

        $params['dateDebut'] = $dateDebut;
        $params['dateFin'] = $dateFin;
        $params['communaute'] = $communauteId;
        //$params['etat'] = $etat;
        $sql .= ' WHERE YEAR(a.daterencontre) between :dateDebut and :dateFin and a.etat in ("audience_rejeter","audience_valider") AND com.id=:communaute';
        $sql .= ' GROUP BY _annee';
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function getAnneeRangeContrat2($dateDebut, $dateFin, $communauteId)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        // $tableEmploye = $this->getTableName(Employe::class, $em);
        // $tableCivilite = $this->getTableName(Civilite::class, $em);
        // $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableCommunaute = $this->getTableName(Communaute::class, $em);
        $tableAudience = $this->getTableName(Audience::class, $em);
        //dd($dateFin);

        if ($dateDebut != null && $dateFin != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, YEAR(a.daterencontre) AS _annee
            FROM {$tableAudience} a
            JOIN {$tableCommunaute} com ON com.id = a.communaute_id
             WHERE YEAR(a.daterencontre) BETWEEN :dateDebut AND :dateFin AND a.etat=:etat AND com.id=:communaute
            GROUP BY _annee
            SQL;
        } elseif ($dateDebut == null && $dateFin != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, th.id AS _niveau_id,th.denomination
            FROM {$tableAudience} d
            JOIN {$tableCommunaute} th ON th.id = e.entreprise_id
            WHERE YEAR(date_debut) =:dateFin
            GROUP BY th.id
            SQL;
        } elseif ($dateDebut != null && $dateFin == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, th.id AS _niveau_id,th.denomination
            FROM {$tableAudience} d
            JOIN {$tableCommunaute} th ON th.id = e.entreprise_id
            WHERE YEAR(date_debut) =:dateDebut
            SQL;
        }

        $params['dateDebut'] = $dateDebut;
        $params['dateFin'] = $dateFin;
        $params['communaute'] = $communauteId;
        $params['etat'] = 'audience_valider';


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
    public function getAnneeRangeContrat1($typeContrat)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $communaute = $this->getTableName(em::class, $em);
        $tableContrat = $this->getTableName(Communaute::class, $em);
        $sql = <<<SQL
        SELECT MIN(YEAR(daterencontre)) AS min_year, MAX(YEAR(daterencontre)) AS max_year
        FROM {$tableContrat}
        WHERE type_contrat_id = :type_contrat
        SQL;
        $params['type_contrat'] = $typeContrat;


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAssociative();
    }


    public function getDataTypeContrat($typeContrat)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        // $tableEmploye = $this->getTableName(Employe::class, $em);
        // $tableGenre = $this->getTableName(Genre::class, $em);
        // $tableNMaitrise = $this->getTableName(NiveauMaitrise::class, $em);
        // $tableHierarchie = $this->getTableName(NiveauHierarchique::class, $em);
        // $tableUnite = $this->getTableName(UniteEmploye::class, $em);
        $tableContrat = $this->getTableName(Audience::class, $em);
        $sql = <<<SQL
        SELECT COUNT(*) AS _total, YEAR(daterencontre)
        FROM {$tableContrat}
        WHERE communaute_id = :type_contrat
        GROUP BY YEAR(daterencontre)
        ORDER BY YEAR(daterencontre) ASC
        SQL;
        $params['type_contrat'] = $typeContrat;
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }




    /**
     * @return Audience[] Returns an array of Audience objects
     */
    public function SelectInterval($form, $to, $commu = null)
    {
        //     $query = $this->createQueryBuilder('a')
        //         ->where('a.CreatedAt > :form')
        //         ->andWhere('a.CreatedAt < :to')
        //         ->orderBy('a.id', 'ASC')
        //         ->setParameter(':form',$form)
        //         ->setParameter(':to',$to);
        //      if ($commu!= null) {
        //        $query->leftJoin('a.communaute','c')
        //        ->leftJoin('a.beneficiaire','b') 
        //        ->andWhere('c.id = :commu')
        //         ->setParameter(':commu', $to);
        //      }   
        //      return $query->getQuery()->getResult();
    }


    //    /**
    //     * @return Audience[] Returns an array of Audience objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Audience
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
