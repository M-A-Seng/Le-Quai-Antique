<?php

namespace App\Exceptions;

use App\Exceptions\ReservationException;

# Réservation n'existe pas, HTTP 404 
class ReservationNotFoundException extends ReservationException {}

# Créneau complet, HTTP 409
class TimeSlotUnavailableException extends ReservationException {}

# Entrées de réservation invalides (date, nb de convives > max), HTTP 422
class InvalidReservationDataException extends ReservationException {}
