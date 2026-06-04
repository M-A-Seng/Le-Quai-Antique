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

/**
 * get_valid_env retourne la variable d'environnement (soit .env, soit getenv() selon lequel des deux est disponible) ou null si aucun n'est définit
 *
 * @param  string $env_var_name
 * @return string|null
 */
function get_valid_env(string $env_var_name): ?string
{
    // getenv() retourne false si la variable n'existe pas
    $value = getenv($env_var_name);
    if ($value !== false) {
        return $value;
    }
    // fallback sur $_ENV
    return $_ENV[$env_var_name] ?? null;
}