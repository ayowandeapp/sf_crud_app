<?php

namespace App\EventListener;

use App\Service\FailedValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener

{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof FailedValidationException) {
            $exceptionData = $exception->getExceptionData();
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'errors' => $exceptionData->getErrors(),
            ], $exceptionData->getStatusCode());

            $event->setResponse($response);
        }
    }
}
