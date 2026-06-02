<?php

namespace App\Exceptions;

/**
 * DbFailureException
 * 
 * - getHttpCode(), 500
 */
class DbFailureException extends ServerException
{
    public function getHttpCode(): int {
        return 500;
    }
}
