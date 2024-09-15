<?php

namespace App\Service;

use Throwable;

class FailedValidationException extends \Exception
{
    private $data;
    public function __construct(ProcessExceptionData $data, string $message = "Validation failed", int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $data->getStatusCode(), $previous);
        $this->data = $data;
    }
    public function getExceptionData(): ProcessExceptionData
    {
        return $this->data;
    }
}
