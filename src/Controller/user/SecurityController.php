<?php

namespace App\Controller\user;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends AbstractController
{
    private $tokenStorage;
    private $entityManager;
    private $eventDispatcher;
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, Security $security, Request $request): Response
    {
        // Get the login error, if any
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
     // Check if the user is restricted
    if ($error && $error->getMessageKey() === 'User  is restricted.') {
        $this->addFlash('error', 'Your account has been restricted. Please contact the administrator.');
    } elseif ($error) {
        $this->addFlash('error', 'Invalid credentials. Please try again.');
    }

    // Render the login page with the last username and any authentication error
    return $this->render('user/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
        // Check if the user is authenticated and their email is not verified
        $user = $security->getUser();
        if ($user && !$user->isVerified()) {
            // Log out the user if they are not verified
            $this->tokenStorage->setToken(null);

            // Add an error message to the session
            $this->addFlash('error', 'Please verify your email address first.');

            // Redirect to the login page
            return $this->redirectToRoute('login');
        }

        // If there was a login error, add a flash message
        if ($error && $error->getMessageKey() === 'User is restricted.') {
            $this->addFlash('error', 'Your account has been restricted. Please contact the administrator.');
        } elseif ($error) {
            $this->addFlash('error', 'Invalid credentials. Please try again.');
        }
    

        // Render the login page with the last username and any authentication error
        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route("/connect/google", name: "connect_google")]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect(['profile', 'email']);
    }
    
    #[Route("/login/check-google", name: "login_google_check")]
    public function loginCheckGoogle(): void
    {
        throw new \LogicException('This should be handled by Symfony security system.');
    }

}