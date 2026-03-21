<?php

namespace App\Exceptions;

/**
 * DataProcessingException
 * 
 * - getUIMessage() return "Données invalides. Veuillez respecter les informations requises."
 * - getHttpCode()
 */
class DataProcessingException extends AbstractBackendException
{
    public function getUIMessage(): string {
        return "Données invalides. Veuillez respecter les informations requises.";
    }

    public function getHttpCode(): int {
        return 422;
    }
}
