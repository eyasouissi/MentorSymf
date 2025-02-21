<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Form\TutorRegistrationType;
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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TutorRegistrationController extends AbstractController
{
    #[Route('/register/tutor', name: 'tutor_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        Security $security,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ): Response {
        // Block authenticated users
        if ($security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse($this->generateUrl('index'));
        }

        $user = new User();
        $form = $this->createForm(TutorRegistrationType::class, $user);
        $form->handleRequest($request);

        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $user->getPlainPassword())
            );

            $user->setRoles(['ROLE_TUTOR']);
            $user->setDateCreation(new \DateTimeImmutable());

            // Handle diploma upload
            $diplomeFile = $form->get('diplome')->getData();
            if ($diplomeFile) {
                $newFilename = uniqid() . '.' . $diplomeFile->guessExtension();
                try {
                    $diplomeFile->move(
                        $this->getParameter('diploma_directory'),
                        $newFilename
                    );
                    $user->setDiplome('assets/uploads/diplomas/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload diploma.');
                }
            }

            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $logger->info('User persisted successfully', [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]);

            $this->addFlash(
                'success',
                'Your data has been successfully stored in the database! User ID: ' . $user->getId()
            );
            $this->addFlash(
                'success',
                'Registration successful! Please check your email to verify your account.'
            );

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

            $this->addFlash('success', 'Registration successful. Please check your email to verify your account.');
            return $this->redirectToRoute('login');
        }

        return $this->render('user/tutor_registration.html.twig', [
            'tutorRegistrationForm' => $form->createView(),
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