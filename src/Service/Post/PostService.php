<?php

namespace App\Service\Post;

use App\DTO\PostDTO;
use App\Entity\Post;
use App\Service\FailedValidationException;
use App\Service\ProcessExceptionData;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostService
{
    public function __construct(private EntityManagerInterface $em) {}
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
        $offset = ($page - 1) * $limit;

        $posts = $this->em->getRepository(Post::class)
            ->createQueryBuilder("post")
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            // ->getResult()
        ;

        //get paginate 
        $paginator = new Paginator($posts, true);
        $totalItems = $paginator->count();
        $totalPages = ceil($totalItems / $limit);

        // Convert the paginated results into an array
        $posts = [];
        foreach ($paginator as $post) {
            $posts[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
            ];
        }

        // Build the next and previous page URLs manually using $request
        $currentUri = $request->getPathInfo(); // This will give you "/posts"
        $queryParams = [];
        $queryParams['limit'] = $limit;

        // Construct the next page URL if there are more pages
        $queryParams['page'] = $page < $totalPages ? $page + 1 : $page;
        $nextPage = $page < $totalPages ? $currentUri . '?' . http_build_query($queryParams) : null;

        // Construct the previous page URL if applicable
        $queryParams['page'] = $page > 1 ? $page - 1 : $page;
        $prevPage = $page > 1 ? $currentUri . '?' . http_build_query($queryParams) : null;

        return [
            'data' => $posts,
            'meta' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'next' => $nextPage,
                'previous' => $prevPage,
            ]
        ];
    }
}
