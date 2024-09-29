<?php

namespace App\Service\Comment;

use App\Entity\Comment;
use App\Service\FailedValidationException;
use App\Service\Pagination\Paginate;
use App\Service\ProcessExceptionData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CommentService
{

    public function __construct(private EntityManagerInterface $em, private Paginate $paginate) {}
    public function saveComment(Comment $comment): Comment
    {
        //map the DTO to the Comment entity
        // $comment = new Comment;
        // $comment->setTitle($commentDTO->getTitle());
        // $comment->setContent($commentDTO->getContent());

        //commit to database
        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function editComment(int $id, Comment $commentDTO)
    {
        $comment = $this->findCommentById($id);

        if (!empty($commentDTO->getText())) $comment->setText($commentDTO->getText());
        $this->em->persist($comment);
        $this->em->flush();
        return $comment;
    }

    public function findCommentById(int $id): ?Comment
    {
        $comment = $this->em->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw new FailedValidationException(new ProcessExceptionData("Comment Not Found", Response::HTTP_NOT_FOUND), "Client Error!");
        }
        return $comment;
    }
}
