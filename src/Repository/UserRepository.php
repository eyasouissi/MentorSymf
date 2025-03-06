<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find users with a specific role.
     * @return array
     */
    // In UserRepository.php

    public function findByRoleStudent(): \Doctrine\ORM\QueryBuilder
{
    return $this->createQueryBuilder('u')
        ->where('u.roles LIKE :role')
        ->setParameter('role', '%"ROLE_STUDENT"%')  // Check if the roles array contains 'ROLE_STUDENT'
        ->orderBy('u.name', 'ASC');  // Optionally, add ordering
}



    // Example for fetching a single user by ID (commented out for reference)
    // public function findOneById($id): ?User
    // {
    //     return $this->createQueryBuilder('u')
    //         ->andWhere('u.id = :id')
    //         ->setParameter('id', $id)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
}