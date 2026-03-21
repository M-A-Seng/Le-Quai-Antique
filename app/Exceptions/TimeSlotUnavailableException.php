<?php

namespace App\Exceptions;

/**
 * TimeSlotUnavailableException
 * 
 * - getUIMessage() 
 * 
 * Default message: "Créneau indisponible, veuillez sélectionner un heure différente."
 */
class TimeSlotUnavailableException extends AbstractFrontendException
{
    public function __construct(string $message = "Créneau indisponible, veuillez sélectionner un heure différente.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->getMessage();
    }
}