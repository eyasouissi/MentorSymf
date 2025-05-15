<?php

namespace App\Repository;

use App\Entity\GroupStudent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<GroupStudent>
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupStudent::class);
    }

    // Existing methods
    public function findAll(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGroupName(string $groupName): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nom_group LIKE :groupName')
            ->setParameter('groupName', '%' . $groupName . '%')
            ->orderBy('g.nom_group', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findGroupsByMeetingDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.date_meet >= :date')
            ->setParameter('date', $date)
            ->orderBy('g.date_meet', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findGroupsByMembers(int $minMembers): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nbr_members >= :minMembers')
            ->setParameter('minMembers', $minMembers)
            ->orderBy('g.nbr_members', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findGroupWithProjects(int $groupId): ?GroupStudent
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.projects', 'p')
            ->addSelect('p')
            ->andWhere('g.id = :groupId')
            ->setParameter('groupId', $groupId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // New methods for creator queries
    public function findAllWithCreator(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.createdBy', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    public function findByCreator(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.createdBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}