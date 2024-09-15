<?php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolation;

class ProcessExceptionData
{
    public function __construct(protected $data, protected int $statusCode) {}

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function getErrors(): array
    {
        $errors = [];
        if (is_string($this->data)) {
            $errors[] = [
                'message' => $this->data,
            ];
        } else {
            foreach ($this->data as $key => $value) {
                /** @var ConstraintViolation $value */
                if ($value instanceof ConstraintViolation) {

                    $errors[] = [
                        'propertyPath' => $value->getPropertyPath(),
                        'message' => $value->getMessage(),
                        'invalidValue' => $value->getInvalidValue()
                    ];
                } else {
                    $errors[] = [
                        'message' => $value,
                    ];
                }
            }
        }
        return $errors;
    }
}
