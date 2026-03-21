<?php

namespace App\Exceptions;

/**
 * DbFailureException
 * 
 * - getHttpCode()
 */
class DbFailureException extends ServerException
{
    public function getHttpCode(): int {
        return 500;
    }
}
