<?php

namespace App\Exceptions;

/**
 * InvalidFieldException
 * 
 * - getUIMessage()
 * 
 * Default message: "Certaines informations sont invalides. Veuillez vérifier votre saisie."
 */
class InvalidFieldException extends AbstractFrontendException 
{
    public function __construct(string $message = "Certaines informations sont invalides. Veuillez vérifier votre saisie.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->getMessage();
    }
}
