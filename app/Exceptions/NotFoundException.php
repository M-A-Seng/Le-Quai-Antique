<?php

namespace App\Exceptions;

use OutOfBoundsException;

/**
 * NotFoundException gère les exceptions liées aux ressource non trouvées.
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
 * 
 * ---------------------
 * Exemples
 * ---------------------
 * Cas 1: Retour utilisateur
 * - throw new NotFoundException("", "Message pour l'utilisateur")
 * - throw new NotFoundException(UIMessage: "Message pour l'utilisateur")
 * 
 * Cas 2: Débogage
 * - throw new NotFoundException("Message")
 * - throw new NotFoundException(message: "Message")
 * 
 * Cas 3: Les deux
 * - throw new NotFoundException("Message", "Message pour l'utilisateur")   
 * - throw new NotFoundException(message: "Message", UIMessage: "Message pour l'utilisateur")   
 */
class NotFoundException extends OutOfBoundsException implements UIMessageProviderExceptionInterface
{
    private string $UIMessage = "";
    private string $defaultUIMessage = "Votre demande n'a pas pu être traitée correctement en raison d'une ressource manquante.";
    
    public function __construct(string $message = "", string $UIMessage = "")
    {
        parent::__construct($message);
        $this->UIMessage = $UIMessage;
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