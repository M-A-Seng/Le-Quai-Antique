<?php

namespace App\Exceptions;

/**
 * DataProcessingException
 * 
 * - getUIMessage() return "Données invalides. Veuillez respecter les informations requises."
 * - getHttpCode(), 422
 */
class DataProcessingException extends AbstractBackendException
{
    public function getUIMessage(): string {
        return "Échec du traitement des données. Veuillez réessayer ou revenir plus tard.";
    }

    public function getHttpCode(): int {
        return 422;
    }
}
