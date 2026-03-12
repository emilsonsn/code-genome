<?php

namespace App\Exceptions;

use RuntimeException;

class RepositoryAnalysisInProgressException extends RuntimeException
{
    public function __construct(
        string $message = 'Repository analysis already in progress.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
