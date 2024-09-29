<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user, TokenManager $tokenManager): JsonResponse
    {

        if (null === $user) {
            return $this->json(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }
        // Generate the token (for demonstration purposes, use a random string)
        $token = bin2hex(random_bytes(16));

        // Save the token and user info to the file
        $tokenManager->saveToken($user->getEmail(), $token);


        return $this->json([
            'email'  => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }

    #[Route('/api/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(TokenManager $tokenManager, UserInterface $user): JsonResponse
    {
        // Assuming the user is authenticated, you can access their username
        if ($user) {
            $username = $user->getUserIdentifier();

            // Remove the token associated with the user
            $tokenManager->removeToken($username);

            return $this->json(['success' => true, 'message' => 'Successfully logged out.'], Response::HTTP_OK);
        }

        return $this->json(['success' => false, 'message' => 'No authenticated user found.'], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, TokenManager $tokenManager): JsonResponse
    {
        // Assuming the token is sent in the request headers or body
        $token = $request->headers->get('Authorization');

        // Check if a token is set
        if ($token) {
            return $this->json(['success' => false, 'message' => 'Token is already set.'], Response::HTTP_BAD_REQUEST);
        }
    }
}
