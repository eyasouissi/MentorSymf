<?php
// src/Repository/CoursesRepository.php

namespace App\Repository;

use App\Entity\Courses;
use App\Entity\Category; // Assurez-vous d'importer l'entité Category
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CoursesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Courses::class);
    }

    /**
     * Recherche et filtre les cours.
     *
     * @param string|null $searchTerm Terme de recherche
     * @param int|null $categoryId ID de la catégorie pour le filtrage
     * @param string $sortField Champ de tri (par défaut : 'id')
     * @param string $sortDirection Direction du tri (ASC ou DESC)
     * @return Courses[]
     */
   // src/Repository/CoursesRepository.php

public function searchAndFilter(?string $searchTerm, ?int $categoryId, string $sortField = 'id', string $sortDirection = 'ASC'): array
{
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.category', 'cat');

    if ($searchTerm) {
        $qb->andWhere('c.title LIKE :searchTerm OR c.description LIKE :searchTerm')
           ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    if ($categoryId !== null) {
        $qb->andWhere('cat.id = :categoryId')
           ->setParameter('categoryId', $categoryId);
    }

    $qb->orderBy('c.' . $sortField, $sortDirection);

    return $qb->getQuery()->getResult();
}

    /**
     * Récupère toutes les catégories disponibles.
     *
     * @return Category[]
     */
    public function findAllCategories(): array
    {
        return $this->getEntityManager()
            ->getRepository(Category::class) // Utilisez le repository de l'entité Category
            ->findAll();
    }


    // src/Repository/CoursesRepository.php

public function getAverageRating(Courses $course): ?float
{
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.ratings', 'r')
        ->select('AVG(r.rating) AS average_rating')
        ->where('c.id = :course_id')
        ->setParameter('course_id', $course->getId())
        ->getQuery();

    $result = $qb->getSingleScalarResult();
    
    return $result ? (float) $result : null;
}


// src/Repository/CoursesRepository.php

public function getOverallAverageRating(): ?float
{
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.ratings', 'r')
        ->select('AVG(r.rating) AS average_rating')
        ->getQuery();

    $result = $qb->getSingleScalarResult();
    
    return $result ? (float) $result : null;
}

}