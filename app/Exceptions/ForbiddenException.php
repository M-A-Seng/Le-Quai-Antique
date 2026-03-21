<?php

namespace App\Exceptions;

/**
 * ForbiddenException
 * 
 * - getUIMessage() return "Nous n'avons pas pu traiter votre demande. Veuillez réessayer ou revenir plus tard."
 * - getHttpCode()
 */
class ForbiddenException extends AbstractBackendException
{
    public function getUIMessage(): string {
        return "Nous n'avons pas pu traiter votre demande. Veuillez réessayer ou revenir plus tard.";
    }

    public function getHttpCode(): int {
        return 403;
    }
}
