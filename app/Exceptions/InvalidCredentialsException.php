<?php

namespace App\Exceptions;

/**
 * InvalidCredentialsException
 * 
 * - getUIMessage()
 * 
 * Defautl message: "Email ou mot de passe invalide."
 */
class InvalidCredentialsException extends AbstractFrontendException 
{
    public function __construct(string $message = "Email ou mot de passe invalide.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->getMessage();
    }
}