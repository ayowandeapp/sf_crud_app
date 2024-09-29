<?php

namespace App\Validation;

use App\Service\FailedValidationException;
use App\Service\ProcessExceptionData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    public function __construct(private ValidatorInterface $validator) {}
    public function validate($dataDTO, string $type = null): mixed
    {
        //validation        
        $errors = $this->validator->validate($dataDTO, groups: $type ? [$type] : '');

        //if validation failed 
        if (count($errors) > 0) {
            //process validation data
            $exceptionData = new processExceptionData($errors, Response::HTTP_BAD_REQUEST);
            throw new FailedValidationException($exceptionData);
        }

        return $dataDTO;
    }
}
