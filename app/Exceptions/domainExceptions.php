<?php

namespace App\Exceptions;

use App\Exceptions\DomainException;

# Echec ou erreur de validation, HTTP 422
class ValidationException extends DomainException {}

# Donnée non trouvée, HTTP 404
class NotFoundException extends DomainException {}

# Accès ou Action non autorisée, HTTP 403
class ForbiddenException extends DomainException {}
