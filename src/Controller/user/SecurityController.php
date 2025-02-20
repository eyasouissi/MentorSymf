<?php

namespace App\Controller\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends AbstractController
{
    private $tokenStorage;

    // Constructor to inject TokenStorageInterface
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, Security $security, Request $request): Response
    {

        // Get the login error, if any
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

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
        if ($error) {
            $this->addFlash('error', 'Invalid credentials. Please try again.');
        }

        // Render the login page with the last username and any authentication error
        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
