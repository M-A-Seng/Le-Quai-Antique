<?php

namespace App\Helpers;

use App\Exceptions\ServerException;

/**
 * Retourne les balises link CSS pour Vite (DEV et PROD)
 *
 * @param  string $filePath
 * @return string
 * 
 * @throws ServerException
 */
function vite_css(string $filePath): string
{
    # DEV
    # resources fournies par le locahost Vite
    $localhost = get_valid_env('VITE_LOCALHOST');

    if (!empty($localhost)) {
        # En DEV, le CSS est injecté par Vite HMR via JS, pas besoin de link
        return '';
    }

    # PROD
    # ressources compilées dans public/assets
    $manifestPath = DIR_ROOT . '/public/assets/.vite/manifest.json';
    if (!file_exists($manifestPath)) {
        throw new ServerException(__FUNCTION__ . ': Vite manifest.json non trouvé.');
    }
    $manifest = json_decode(file_get_contents($manifestPath), true);
    
    if (!isset($manifest[$filePath])) {
        throw new ServerException(__FUNCTION__ . ": Vite asset non trouvé: $filePath");
    }
    $asset = $manifest[$filePath];

    $html = '';
    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $css) {
            $html .= '<link rel="stylesheet" href="/assets/' . $css . '">' . PHP_EOL;
        }
    }
    return $html;
}

/**
 * Retourne la balise script type="module" pour Vite (DEV et PROD)
 *
 * @param  string $filePath
 * @return string
 * 
 * @throws ServerException
 */
function vite_js(string $filePath): string
{
    # DEV
    # resources fournies par le locahost Vite
    $localhost = get_valid_env('VITE_LOCALHOST');

    if (!empty($localhost)) {
        # Injecte Vite client pour HMR + fichier JS
        return
            '<script type="module" src="' . $localhost . '/@vite/client"></script>' . PHP_EOL .
            '<script type="module" src="' . $localhost . '/' . $filePath . '"></script>' . PHP_EOL;
    }

    # PROD
    # ressources compilées dans public/assets
    $manifestPath = DIR_ROOT . '/public/assets/.vite/manifest.json';
    if (!file_exists($manifestPath)) {
        throw new ServerException(__FUNCTION__ . ': Vite manifest.json non trouvé.');
    }
    $manifest = json_decode(file_get_contents($manifestPath), true);

    if (!isset($manifest[$filePath])) {
        throw new ServerException(__FUNCTION__ . ": Vite asset non trouvé: $filePath");
    }
    $asset = $manifest[$filePath];

    return '<script type="module" src="/assets/' . $asset['file'] . '"></script>' . PHP_EOL;
}
