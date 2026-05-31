<?php

namespace App\Exceptions;

/**
 * InvalidFileException 
 * 
 * - getUIMessage()
 * 
 * @param string $UImessage PAS DE MESSAGE TECHNIQUE (affichage client) Message par défaut: "Fichier Invalide."
 * @param string $message 
 * 
 */
class InvalidFileException extends AbstractFrontendException 
{
    public function __construct(private string $UImessage = 'Fichier Invalide.', string $message = "Fichier Invalide.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->UImessage;
    }
}