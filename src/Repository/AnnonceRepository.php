<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * @extends ServiceEntityRepository<Annonce>
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }
    public function countByDate(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DATE(date_a) AS date, COUNT(id) AS count
            FROM annonce
            GROUP BY DATE(date_a)
            ORDER BY DATE(date_a) ASC
        ';
        $statement = $connection->executeQuery($sql);
    
        return $statement->fetchAllAssociative();
    }
    
public function countByMonth(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT MONTH(date_a) as month, COUNT(id) as count
            FROM annonce
            GROUP BY MONTH(date_a)
        ';
        $statement = $connection->executeQuery($sql);

        return $statement->fetchAllAssociative();
    }
    //    /**
    //     * @return Annonce[] Returns an array of Annonce objects
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

    //    public function findOneBySomeField($value): ?Annonce
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findBySearchTermAndOrder(string $searchTerm, string $orderBy, int $page = 1): Paginator
{
    $qb = $this->createQueryBuilder('a');

    // Recherche sur titre_a et description_a
    if (!empty($searchTerm)) {
        $qb->andWhere('a.titre_a LIKE :term OR a.description_a LIKE :term')
           ->setParameter('term', '%' . $searchTerm . '%');
    }

    // Liste blanche des champs autorisés pour le tri
    $allowedOrderFields = ['titre_a', 'date_a']; // ajoute d'autres si nécessaire
    if (!in_array($orderBy, $allowedOrderFields)) {
        $orderBy = 'date_a'; // tri par défaut
    }

    $qb->orderBy('a.' . $orderBy, 'DESC');

    // Pagination : 10 résultats par page
    $query = $qb->getQuery()
        ->setFirstResult(($page - 1) * 10)
        ->setMaxResults(10);

    return new Paginator($query);
}
}
