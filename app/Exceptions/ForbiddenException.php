<?php

namespace App\Exceptions;

use DomainException;

/**
 * ForbiddenException, Accès ou Action non autorisée, HTTP 403
 */
class ForbiddenException extends DomainException {}
