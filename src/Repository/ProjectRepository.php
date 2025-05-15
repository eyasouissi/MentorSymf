<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{ 
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }


    public function countProjectsByDifficulty()
    {
        return $this->createQueryBuilder('p')
            ->select('p.difficulte, COUNT(p.id) as project_count')
            ->groupBy('p.difficulte')
            ->getQuery()
            ->getResult();
    }
    

    
    public function getProjectsByDay()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT COUNT(id) as project_count, DATE(date_creation_project) as project_date 
            FROM project 
            GROUP BY DATE(date_creation_project)
            ORDER BY project_date ASC
        ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
    
        return $resultSet->fetchAllAssociative();
    }


    public function getProjectsCountByMonth()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT COUNT(id) as project_count, DATE_FORMAT(date_creation_project, '%Y-%m') as project_month 
            FROM project 
            GROUP BY project_month
            ORDER BY project_month ASC
        ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
    
        return $resultSet->fetchAllAssociative();
    }
    


    

    //    /**
    //     * @return Project[] Returns an array of Project objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
