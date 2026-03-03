<?php

namespace App\Exceptions;

/**
 * EmailAlreadyUsedException, Email déjà utilisé, HTTP 409
 */
class EmailAlreadyUsedException extends ForbiddenException {}
