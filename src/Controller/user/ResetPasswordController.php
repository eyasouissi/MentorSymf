<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function reset(
     
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    string $token
    ): Response {
    // Find the user by the reset token
    $user = $entityManager->getRepository(User::class)->findOneBy(['password_reset_token' => $token]);
    dump($user->getPasswordResetToken(), $token);

    if (!$user) {
        $this->addFlash('error', 'Invalid or expired token.');
        return $this->redirectToRoute('login');
    }

   

    // Create and handle the reset password form
    $form = $this->createForm(ResetPasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the plain password from the form
        $plainPassword = $form->get('plainPassword')->getData();
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

        // Update the user's password and reset the token
        $user->setPassword($hashedPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordResetRequestedAt(null);

        // Save the updated user data to the database
        $entityManager->flush();

        // Add success message and redirect to login
        $this->addFlash('success', 'Your password has been reset successfully.');
        return $this->redirectToRoute('login');
    }

    // Render the reset password form
    return $this->render('user/reset_password.html.twig', [
        'resetPasswordForm' => $form->createView(),
    ]);
}

}

