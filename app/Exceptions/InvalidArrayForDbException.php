<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * InvalidArrayForDbException, Tableau invalide pour la base de donnée, HTTP 422
 */
class InvalidArrayForDbException extends InvalidArgumentException {}
