<?php

namespace App\Exceptions;

/**
 * AccountDisabledException
 * 
 * - getUIMessage() return "Votre compte est actuellement suspendu."
 * - getHttpCode()
 */
class AccountDisabledException extends ForbiddenException 
{
    public function getUIMessage(): string {
        return "Votre compte est actuellement suspendu.";
    }

    public function getHttpCode(): int {
        return 403;
    }
}