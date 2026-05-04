<?php

namespace App\Exceptions;

/**
 * InvalidReservationException PAS DE MESSAGE TECHNIQUE DANS LE MESSAGE D'EXCEPTION (affichage client)
 * 
 * - getUIMessage()
 * 
 * Default message: "Une erreur est survenue. Veuillez réessayer ou revenir plus tard."
 */
class InvalidReservationException extends AbstractFrontendException 
{
    public function __construct(string $message = "Nous n'avons pas réussi à traiter votre réservation. Veuillez essayer à nouveau.")
    {
        parent::__construct($message);
    }

    public function getUIMessage(): string {
        return $this->getMessage();
    }
}