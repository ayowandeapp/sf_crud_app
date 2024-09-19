<?php

namespace App\Service\Post;

use App\DTO\PostDTO;
use App\Entity\Post;
use App\Service\FailedValidationException;
use App\Service\Pagination\Paginate;
use App\Service\ProcessExceptionData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostService
{
    public function __construct(private EntityManagerInterface $em, private Paginate $paginate) {}
    public function savePost(PostDTO $postDTO): Post
    {
        //map the DTO to the Post entity
        $post = new Post;
        $post->setTitle($postDTO->getTitle());
        $post->setContent($postDTO->getContent());

        //commit to database
        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function findPostById(int $id): ?Post
    {
        $post = $this->em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw new FailedValidationException(new ProcessExceptionData("Post Not Found", Response::HTTP_NOT_FOUND), "Client Error!");
        }
        return $post;
    }

    public function deletePost(int $id): ?Post
    {
        $post = $this->findPostById($id);
        $this->em->remove($post);
        $this->em->flush();
        return null;
    }

    public function editPost(int $id, postDTO $postDTO)
    {
        $post = $this->findPostById($id);

        if (!empty($postDTO->getTitle())) $post->setTitle($postDTO->getTitle());
        if (!empty($postDTO->getContent())) $post->setContent($postDTO->getContent());
        $this->em->persist($post);
        $this->em->flush();
        return $post;
    }

    public function getPost($request): array
    {
        $page = $request->query->get("page", 1);
        $limit = $request->query->get("limit", 2);

        $posts = $this->em->getRepository(Post::class)
            ->createQueryBuilder("post")
            ->getQuery()
            // ->getResult()
        ;
        // dd($posts);

        return $this->paginate->paginate($posts, $page, $limit);
    }
}
