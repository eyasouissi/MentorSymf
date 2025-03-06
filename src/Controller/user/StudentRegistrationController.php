<?php
namespace App\Controller\user;

use App\Entity\User;
use App\Form\StudentRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Use this interface
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class StudentRegistrationController extends AbstractController
{
    #[Route('/register/student', name: 'student_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,  
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): Response {

        $user = new User();
        $form = $this->createForm(StudentRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash password
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $user->getPlainPassword())
            );

            // Set roles and other user data
            $user->setRoles(['ROLE_STUDENT']);
            $user->setDateCreation(new \DateTimeImmutable());

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);
            $user->setIsVerified(false);

            // Persist user to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Generate verification URL
            $verificationUrl = $urlGenerator->generate(
                'verify_email', 
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );




 

            // Create the email
            $email = (new Email())
                ->from('your_email@example.com')  // Change this to your email
                ->to($user->getEmail())
                ->subject('Please Verify Your Email Address')
                ->html(
                    $this->renderView('user/verification_email.html.twig', [
                        'verificationUrl' => $verificationUrl,
                        'user' => $user,
                    ])
                );

            // Send the email
            $mailer->send($email);

            // Add a success message
            

            return $this->redirectToRoute('login');
        }

        return $this->render('user/student_registration.html.twig', [
            'studentRegistrationForm' => $form->createView(),
        ]);
    }
}
