<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Response\UserProfileResponse;
use App\Service\User\UserService;
use App\Validation\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private Validator $validator,
        private UserService $userService
    ) {}
    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        // $user = new User();
        // $user->setEmail('test@test.com');
        // $user->setPassword(
        //     $this->hasher->hashPassword($user, '12345')
        // );
        // $this->em->persist($user);
        // $this->em->flush();

        // $user = new User();
        // $user->setEmail('wande@test.com');
        // $user->setPassword(
        //     $this->hasher->hashPassword($user, '12345')
        // );
        // $this->em->persist($user);
        // $this->em->flush();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/api/add/user-profile', name: 'add_user_profile')]
    public function addUserProfile(Request $request): JsonResponse
    {
        $userProfileDTO = $this->serializer->deserialize($request->getContent(), UserProfile::class, 'json');

        $userProfileDTO->setUser($this->getUser());

        $userProfileDTO = $this->validator->validate($userProfileDTO);

        $userProfile = $this->userService->updateUserProfile($userProfileDTO);

        return new JsonResponse((new UserProfileResponse($userProfile))->toArray(), Response::HTTP_CREATED);
    }


    #[Route('/api/follow/{userToFollow}', name: 'app_follow', methods: Request::METHOD_GET)]
    public function follow(User $userToFollow): ?JsonResponse
    {
        try {
            /**
             * @var User
             */
            $user = $this->getUser();
            if ($user->getId() === $userToFollow->getId()) {
                return null;
            }
            $user->addFollow($userToFollow);
            $this->em->persist($user);
            $this->em->flush();
            return new JsonResponse("User \"{$user->getEmail()}\" follows {$userToFollow->getEmail()} ", 200);
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage(), 400);
        }
    }

    #[Route('/api/unfollow/{userToFollow}', name: 'app_unfollow',  methods: Request::METHOD_GET)]
    public function UnFollow(User $userToFollow): ?JsonResponse
    {
        try {
            /**
             * @var User
             */
            $user = $this->getUser();
            $user->removeFollow($userToFollow);
            $this->em->persist($user);
            $this->em->flush();
            return new JsonResponse("User \"{$user->getEmail()}\" unfollowed {$userToFollow->getEmail()} ", 200);
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage(), 400);
        }
    }


    #[Route('/api/follows', name: 'app_follows',  methods: Request::METHOD_GET)]
    public function getFollows(): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $follows = [];
        if (null !== $user) {
            /**
             * @var array
             */
            $follows = $user->getFollows()->toArray();
        }


        return new JsonResponse($follows, 200);
    }

    #[Route('/api/followers', name: 'app_followers',  methods: Request::METHOD_GET)]
    public function getFollowers(): JsonResponse
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $followers = [];
        if (null !== $user) {
            /**
             * @var array
             */
            $followers = $user->getFollowers()->toArray();
        }

        return new JsonResponse($followers, 200);
    }
}
