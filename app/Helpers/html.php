<?php

namespace App\Helpers;

/**
 * html retourne le string safe pour html
 *
 * @param  string $value
 * @return string
 */
function html(string $value): string
{
    if (empty(trim($value))) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}