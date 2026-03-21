<?php

namespace App\Exceptions;

use Exception;

/**
 * AbstractBackendException gère les exceptions internes aux processus métiers. Utilisez uniquement getUIMessage() pour les retours utilisateurs ; et getMessage() pour le débogage.
 * 
 * - getUIMessage()
 * - getHttpCode()
 */
abstract class AbstractBackendException extends Exception implements UIMessageProviderExceptionInterface
{
    abstract public function getUIMessage(): string;
    abstract public function getHttpCode(): int;
}