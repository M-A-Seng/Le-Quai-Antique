<?php

namespace App\Exceptions;

use DomainException;

/**
 * TimeSlotUnavailableException, Créneau complet, HTTP 409
 */
class TimeSlotUnavailableException extends DomainException {}