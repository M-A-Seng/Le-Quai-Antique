<?php

namespace App\Exceptions;

/**
 * InvalidReservationDataException, Entrées invalides pour réservation (ex: date, nb de convives > max), HTTP 422
 */
class InvalidReservationDataException extends ValidationException {}
