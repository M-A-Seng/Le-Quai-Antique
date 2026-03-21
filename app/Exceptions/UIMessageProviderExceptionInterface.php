<?php

namespace App\Exceptions;

/**
 * UIMessageProviderExceptionInterface implémente getUIMessage()
*/
interface UIMessageProviderExceptionInterface
{
    public function getUIMessage(): string;
}