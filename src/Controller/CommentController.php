<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Response\CommentResponse;
use App\Service\Comment\CommentService;
use App\Service\FailedValidationException;
use App\Service\Post\PostService;
use App\Service\ProcessExceptionData;
use App\Validation\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends AbstractController
{
    public function __construct(
        private PostService $postService,
        private SerializerInterface $serializer,
        private CommentService $commentService
    ) {}
    #[Route('/comment', name: 'app_comment')]
    public function index(): JsonResponse
    {


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CommentController.php',
        ]);
    }


    #[Route('/save/comment', name: 'app_save_comment', methods: [Request::METHOD_POST])]
    public function saveComment(Request $request, Validator $validator): JsonResponse
    {
        // Deserialize JSON request content into PostDTO

        $dataDTO = $this->serializer->deserialize($request->getContent(), Comment::class, 'json');

        $post_id = $request->toArray()['post'];
        // dd(is_int($post_id));
        if (!is_int($post_id)) {
            throw new FailedValidationException(new ProcessExceptionData("Post Not Found", Response::HTTP_NOT_FOUND), "Client Error!");
        }
        $post = $this->postService->findPostById($post_id);

        $dataDTO->setPost($post);

        $validated = $validator->validate($dataDTO, type: 'create');

        $comment = $this->commentService->saveComment($validated);

        return new JsonResponse((new CommentResponse($comment))->toArray(), 201);
    }

    #[Route(path: '/edit/{id}/comment', name: 'app_edit_comment', methods: [Request::METHOD_PUT])]
    public function editComment(Request $request, int $id, Validator $validator): JsonResponse
    {
        // Deserialize JSON request content into PostDTO

        $dataDTO = $this->serializer->deserialize($request->getContent(), Comment::class, 'json');

        $commentDTO = $validator->validate($dataDTO, 'edit');

        $comment = $this->commentService->editComment($id, $commentDTO);

        return new JsonResponse((new CommentResponse($comment))->toArray(), 201);
    }
}
