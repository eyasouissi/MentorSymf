<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserRegistrationController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash password
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $user->getPlainPassword())
            );

            // Set role (either student or tutor)
            $role = $form->get('roles')->getData();
if (is_array($role)) {
    $role = $role[0] ?? 'ROLE_STUDENT'; // Take the first role or default to 'ROLE_STUDENT'
}

if ($role === 'ROLE_TUTOR') {
    $user->setRoles(['ROLE_TUTOR']);
} else {
    $user->setRoles(['ROLE_STUDENT']);
}

            
            $user->setDateCreation(new \DateTimeImmutable());

            // Set date of registration
            $user->setDateCreation(new \DateTimeImmutable());

            // Handle any specific logic for each role
            if ($role === 'ROLE_STUDENT') {
                // Additional logic for students if needed
            } elseif ($role === 'ROLE_TUTOR') {
                // Handle tutor-specific logic like uploading diplomas
                $diplomeFile = $form->get('diplome')->getData();
                if ($diplomeFile) {
                    $newFilename = uniqid() . '.' . $diplomeFile->guessExtension();
                    try {
                        $diplomeFile->move(
                            $this->getParameter('diploma_directory'),
                            $newFilename
                        );
                        $user->setDiplome('assets/uploads/diplomas/' . $newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Failed to upload diploma.');
                    }
                }
            }

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);
            $user->setIsVerified(false);

            // Persist user to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Send verification email
            $verificationUrl = $urlGenerator->generate(
                'verify_email',
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('your_email@example.com')
                ->to($user->getEmail())
                ->subject('Please Verify Your Email Address')
                ->html(
                    $this->renderView('user/verification_email.html.twig', [
                        'verificationUrl' => $verificationUrl,
                        'user' => $user,
                    ])
                );

            $mailer->send($email);

            $this->addFlash('success', 'Registration successful. Please check your email to verify your account.');
            return $this->redirectToRoute('admin');
        }

        return $this->render('back/admin/registration.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Invalid verification token.');
            return $this->redirectToRoute('login');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your email has been verified successfully. You can now log in.');
        return $this->redirectToRoute('login');
    }
}
