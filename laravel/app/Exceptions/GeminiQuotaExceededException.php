<?php

namespace App\Exceptions;

use Exception;

class GeminiQuotaExceededException extends Exception
{
    public function __construct(string $message = "O limite de uso da API Gemini foi excedido. Por favor, tente novamente mais tarde.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
