<?php
namespace App\Repository;

use App\Entity\ProhibitedWord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProhibitedWordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProhibitedWord::class);
    }

    // Add this custom method
    public function findAllOrderedByCategory()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.category', 'ASC')
            ->addOrderBy('p.severity', 'DESC')
            ->getQuery()
            ->getResult();
    }
}