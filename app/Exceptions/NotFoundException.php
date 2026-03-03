<?php

namespace App\Exceptions;

use OutOfBoundsException;

/**
 * NotFoundException, Donnée non trouvée, HTTP 404
 */
class NotFoundException extends OutOfBoundsException {}