<?php

namespace App\Exceptions;

/**
 * RequireLoginException.
 * 
 * Vous pouvez adresser un message personnalisé à l'utilisateur avec $UIMessage, ou laisser vide pour le message par défaut.
 * 
 * - `getUIMessage()`
 * - `getHttpCode()`, 403
 * 
 * Message par défaut: "Vous devez vous connecter pour accéder à cette page.".
 */
class RequireLoginException extends ForbiddenException
{
    private string $defaultUIMessage = "Vous devez vous connecter pour accéder à cette page.";
    
    public function __construct(string $message = "", private string $UIMessage = '') 
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return  !empty($this->UIMessage) 
                ? $this->UIMessage 
                : $this->defaultUIMessage;
    }

    public function getHttpCode(): int {
        return 403;
    }
}