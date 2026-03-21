<?php

namespace App\Exceptions;

use Exception;

/**
 * AbstractFrontendException gère les exceptions de type **Feedback Utilisateur**. Veuillez ne surtout pas mettre de message technique en paramètre. getMessage() et getUIMessage() **ne sont pas** dissociés.
 * 
 * - getUIMessage
 * 
 * Attention: Cette classe ne renvoie pas de code http
 */
abstract class AbstractFrontendException extends Exception implements UIMessageProviderExceptionInterface
{
    abstract public function getUIMessage(): string;
}