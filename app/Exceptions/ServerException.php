<?php

namespace App\Exceptions;

/**
 * ServerException
 * 
 * - getUIMessage() return "Une erreur interne est survenue. Veuillez réessayer ou revenir plus tard."
 * - getHttpCode(), 500
 */
class ServerException extends AbstractBackendException
{
    public function getUIMessage(): string {
        return "Une erreur interne est survenue. Veuillez réessayer ou revenir plus tard.";
    }

    public function getHttpCode(): int {
        return 500;
    }
}