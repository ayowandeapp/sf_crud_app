<?php

namespace App\Middleware;

use App\Service\FailedValidationException;
use App\Service\ProcessExceptionData;
use App\Service\TokenManager;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function supports(Request $request): ?bool
    {
        if (!$request->headers->has('Authorization') && $request->getPathInfo() !== '/api/login') {
            throw new FailedValidationException(new ProcessExceptionData('Invalid token111.', 401));
        }
        return $request->headers->has('Authorization');
        // return true;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('Authorization');

        // Check for a user with this token in the file
        foreach ($this->tokenManager->getAllTokens() as $username => $storedToken) {
            if ($token === $storedToken) {
                // dd('hehe');
                return new Passport(
                    new UserBadge($username),
                    new CustomCredentials(
                        function ($credentials, UserInterface $user) {
                            return true;
                        },
                        $token
                    )
                );
            }
        }

        throw new Exception('Invalid token.');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continue processing the request
    }
}
