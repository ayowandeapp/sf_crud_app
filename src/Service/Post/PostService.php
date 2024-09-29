<?php

namespace App\Service\Post;

use App\DTO\PostDTO;
use App\Entity\Post;
use App\Entity\User;
use App\Service\FailedValidationException;
use App\Service\Pagination\Paginate;
use App\Service\ProcessExceptionData;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Paginate $paginate,
        private Security $security
    ) {}
    public function savePost(PostDTO $postDTO): Post
    {
        $user = $this->security->getUser();
        //map the DTO to the Post entity
        $post = new Post;
        $post->setTitle($postDTO->getTitle());
        $post->setContent($postDTO->getContent());

        $post->setAuthor($user);

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

    
    ////// create reusable post query ///////
    private function findAllQuery(
        bool $withComments = false,
        bool $withLikes = false,
        bool $withAuthors = false,
        bool $withProfiles = false
    ): QueryBuilder
    {
        $query = $this->em->getRepository(Post::class)
                    ->createQueryBuilder('post')
                    ;
        if($withComments){
            $query->leftJoin('post.comments', 'comments')// Eager load comments
            ->addSelect('comments');// Add the comments to the select clause
        }

        if($withLikes){
            $query->leftJoin('post.likedBy', 'l')
            ->addSelect('l');
        }
        
        if($withAuthors || $withProfiles){
            $query->leftJoin('post.author', 'author')
            ->addSelect('author');
        }
        
        if($withProfiles){
            $query->leftJoin('author.userProfile', 'up')
            ->addSelect('up');
        }

        return $query->orderBy('p.createdDate', 'DESC');

    }
    public function findPostWithComments(int $id): ?array
    {
        $post = $this->em->getRepository(Post::class)
            ->createQueryBuilder('post')
            ->addSelect('comments')
            ->leftJoin('post.comments', 'comments')
            ->where('post.id = :post_id')
            ->setParameter('post_id', $id)
            ->getQuery()
            ->getArrayResult();

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

        $posts = $this->findAllQuery(withComments: true)
            ->addSelect("COUNT(comments) as comment_count")
            ->groupBy("post.id")
            ->getQuery()
            // ->getResult()
        ;

        return $this->paginate->paginate($posts, $page, $limit);
    }
    
    public function findAllByAuthor($request, int | User $author): array
    {
        $page = $request->query->get("page", 1);
        $limit = $request->query->get("limit", 2);

        $posts = $this->findAllQuery(withComments: true, withAuthors: true)
                    ->where('author = :author')
                    ->setParameter(
                        'author', 
                        $author instanceof User ? $author->getId() : $author
                        )
                    ->addSelect("COUNT(comments) as comment_count")
                    ->groupBy("post.id")
                    ->getQuery()
        ;

        return $this->paginate->paginate($posts, $page, $limit);
    }

    
    public function findPostWithMinLikes($request, int $likeCount): array
    {
        $page = $request->query->get("page", 1);
        $limit = $request->query->get("limit", 2);

        $postIds = $this->findAllQuery(withLikes: true)
                    ->select('post.id')
                    ->having('COUNT(l) >= :likeCount')
                    ->setParameter('likeCount', $likeCount)
                    ->groupBy("post.id")
                    ->getQuery()
                    ->getResult(Query::HYDRATE_SCALAR_COLUMN)
        ;
        $posts = $this->findAllQuery(withLikes: true, withComments:true)
                        ->where('post.id IN (:postIds)')
                        ->setParameter('postIds', $postIds)
                        ->getQuery()
        ;

        return $this->paginate->paginate($posts, $page, $limit);
    }

    
    public function findPostFromFollowed($request): array
    {
        $page = $request->query->get("page", 1);
        $limit = $request->query->get("limit", 2);

        /**
         * @var User
         */
        $currentUser = $this->security->getUser();
        $authors = $currentUser->getFollows();
        
        $posts = $this->findAllQuery(withAuthors: true, withLikes: true, withComments:true)
                        ->where('post.author IN (:authors)')
                        ->setParameter('authors', $authors)
                        ->getQuery()
        ;

        return $this->paginate->paginate($posts, $page, $limit);
    }
}
