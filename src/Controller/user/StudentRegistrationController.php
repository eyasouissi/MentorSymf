<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Form\StudentRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;


class StudentRegistrationController extends AbstractController
{
    #[Route('/register/student', name: 'student_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,  
        EntityManagerInterface $entityManager,
        Security $security,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {

        $user = new User();
        $form = $this->createForm(StudentRegistrationType::class, $user);
        $form->handleRequest($request);

        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $user->getPlainPassword())
            );

            
            // Set role for the user
            $user->setRoles(['ROLE_STUDENT']);
            $user->setDateCreation(new \DateTimeImmutable());
            

       

            // Generate verification token and set status
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);
            $user->setIsVerified(false);

            // Persist the user and save to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Send verification email
            $verificationUrl = $urlGenerator->generate(
                'verify_email',
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('your_email@gmail.com')
                ->to($user->getEmail())
                ->subject('Please Verify Your Email Address')
                ->html(
                    $this->renderView('user/verification_email.html.twig', [
                        'verificationUrl' => $verificationUrl,
                        'user' => $user,
                    ])
                );

            $mailer->send($email);

            // Add success flash message and redirect to login page
            $this->addFlash('success', 'Registration successful. Please check your email to verify your account.');
            return $this->redirectToRoute('login');
        }

        // Render the registration form view
        return $this->render('user\student_registration.html.twig', [
            'studentRegistrationForm' => $form->createView(),
        ]);
    }
    #[Route('/verify/{token}', name: 'verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $logger->info('Verification token received: ' . $token);
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $logger->error('Invalid verification token: ' . $token);
            $this->addFlash('error', 'Invalid verification token.');
            return $this->redirectToRoute('login');
        }

        $logger->info('User found: ' . $user->getEmail());
        $user->setIsVerified(true);
        $user->setVerificationToken(null);

        $entityManager->persist($user);
        $entityManager->flush();

        $logger->info('User verified: ' . $user->getEmail());
        $this->addFlash('success', 'Your email has been verified successfully. You can now log in.');

        return $this->redirectToRoute('login');
    }
}
