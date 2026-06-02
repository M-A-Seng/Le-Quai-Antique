<?php

namespace App\Exceptions;

/**
 * EmailAlreadyUsedException PAS DE MESSAGE TECHNIQUE DANS LE MESSAGE D'EXCEPTION (affichage client)
 * 
 * - getUIMessage() 
 * 
 * Default message: "Cet email est déjà utilisé par un utilisateur."
 */
class EmailAlreadyUsedException extends AbstractFrontendException 
{
    public function __construct(string $message = "Cet email est déjà utilisé par un utilisateur.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->getMessage();
    }
}
