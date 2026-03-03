<?php

namespace App\Exceptions;

use DomainException;

/**
 * ValidationException, Echec ou erreur de validation, HTTP 422
 */
class ValidationException extends DomainException {}