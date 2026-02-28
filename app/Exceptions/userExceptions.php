<?php

namespace App\Exceptions;

use App\Exceptions\UserException;

# Identifiant invalide, HTTP 401
class InvalidCredentialsException extends UserException {}

# Compte suspendu, HTTP 403
class AccountDisabledException extends UserException {}

# Email déjà utilisé, HTTP 409
class EmailAlreadyUsedException extends UserException {}
