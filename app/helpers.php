<?php

namespace App;

/**
 * html retourne le string safe pour html
 *
 * @param  string $value
 * @return string
 */
function html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}