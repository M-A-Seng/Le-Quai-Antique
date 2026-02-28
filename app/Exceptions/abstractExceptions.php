<?php

namespace App\Exceptions;

use Exception;

abstract class DomainException extends Exception {}
abstract class UserException extends Exception {}
abstract class ReservationException extends Exception {}