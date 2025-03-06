<?php
// src/Service/ViolationTracker.php
namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ViolationTracker {
    private $em;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function handleViolation(User $user, array $violations) {
        $totalScore = array_sum(array_column($violations, 'severity'));
        
        // Update user score
        $user->addViolationScore($totalScore);
        $this->em->persist($user);
        
        // Take action based on cumulative score
        $actions = [];
        if($user->getViolationScore() >= 10) {
            $actions[] = 'account_suspension';
            $user->setIsSuspended(true);
        }
        if($totalScore >= 5) {
            $actions[] = 'post_moderation';
        }
        
        $this->em->flush();
        return $actions;
    }
}