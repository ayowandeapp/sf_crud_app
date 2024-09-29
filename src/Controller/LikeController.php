<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LikeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}
    #[Route('/api/like/{post}', name: 'app_like', methods: Request::METHOD_GET)]
    public function like(Post $post): ?JsonResponse
    {
        try {
            $user = $this->getUser();
            $post->addLikedBy($user);
            $this->em->persist($post);
            $this->em->flush();
            return new JsonResponse("Post \"{$post->getTitle()}\" Liked ", 200);
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage(), 400);
        }
    }

    #[Route('/api/unlike/{post}', name: 'app_unlike',  methods: Request::METHOD_GET)]
    public function unLike(Post $post): ?JsonResponse
    {
        try {
            $user = $this->getUser();
            $post->removeLikedBy($user);
            $this->em->persist($post);
            $this->em->flush();
            return new JsonResponse("Post \"{$post->getTitle()}\" UnLiked ", 200);
        } catch (\Exception $th) {
            return new JsonResponse($th->getMessage(), 400);
        }
    }
}
