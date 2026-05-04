<?php

namespace App\Exceptions;

/**
 * ForbiddenException
 * 
 * Vous pouvez adresser un message personnalisé à l'utilisateur avec $UIMessage, ou laisser vide pour le message par défaut.
 * 
 * - getUIMessage() return "Nous n'avons pas pu traiter votre demande. Veuillez réessayer ou revenir plus tard."
 * - getHttpCode(), 403
 */
class ForbiddenException extends AbstractBackendException
{
    private string $defaultUIMessage = "Nous n'avons pas pu traiter votre demande. Veuillez réessayer ou revenir plus tard.";
    
    public function __construct(string $message = "", private string $UIMessage = '') 
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return  !empty($this->UIMessage )
                ? $this->UIMessage
                : $this->defaultUIMessage;
    }

    public function getHttpCode(): int {
        return 403;
    }
}
