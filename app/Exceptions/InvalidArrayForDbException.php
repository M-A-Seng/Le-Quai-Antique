<?php

namespace App\Exceptions;

/**
 * InvalidArrayForDbException
 * 
 * - getHttpCode(), 422
 */
class InvalidArrayForDbException extends DataProcessingException
{
    public function getHttpCode(): int {
        return 422;
    }
}

