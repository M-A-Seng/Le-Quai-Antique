<?php

namespace App\Exceptions;

use OutOfBoundsException;

/**
 * NotFoundException gère les exceptions liées aux ressource non trouvées. VOUS POUVEZ DEFINIR UN MESSAGE SEPARÉ POUR LE DEBUG ($message) ET POUR LE CLIENT ($UIMessage).
 * 
 * ---------------------
 * Méthodes
 * ---------------------
 * 
 * - `getUIMessage()` pour les retours utilisateurs (retourne la variable $UIMessage si définit, sinon un message par défaut)
 * - `getDebugMessage()` pour le débogage (retourne la variable $message. Interchangeable avec getMessage())
 * - `getHttpCode()`, 404
 * 
 * Message UI par défaut: "*Votre demande n'a pas pu être traitée correctement en raison d'une ressource manquante.*" 
 */
class NotFoundException extends OutOfBoundsException implements UIMessageProviderExceptionInterface
{
    private string $defaultUIMessage = "Votre demande n'a pas pu être traitée correctement en raison d'une ressource manquante.";
    
    public function __construct(string $message = "", private string $UIMessage = "")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->UIMessage ?? $this->defaultUIMessage;
    }

    public function getDebugMessage(): string {
        return $this->getMessage();
    }

    public function getHttpCode(): int {
        return 404;
    }
}