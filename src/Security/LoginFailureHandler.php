<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = $exception->getMessageKey();

        if ($message === 'User  is restricted.') {
            $message = 'Your account has been restricted. Please contact the administrator.';
        }

        $request->getSession()->set('login_error', $message);

        return new Response($message, Response::HTTP_UNAUTHORIZED);
    }
}