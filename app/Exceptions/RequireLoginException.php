<?php

namespace App\Exceptions;

/**
 * RequireLoginException
 * 
 * - `getUIMessage()`
 * - `getHttpCode()`, 403
 */
class RequireLoginException extends ForbiddenException
{
    public function getUIMessage(): string {
        return "Vous devez vous connecter pour accéder à cette page.";
    }

    public function getHttpCode(): int {
        return 403;
    }
}