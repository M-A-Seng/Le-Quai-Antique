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
    # ressources via le localhost dans hot
    $viteDev = getVite('local');
    if (APPENV === 'dev' && $viteDev) {
        # En DEV, le CSS est injecté par Vite HMR via JS, pas besoin de link
        return '';
    }

    # PROD
    # ressources compilées dans public/assets
    $manifest = getVite('manifest');
    if (!isset($manifest[$filePath])) {
        throw new ServerException(__FUNCTION__ . ": Vite asset not found: $filePath");
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
    # ressources via localhost vite
    if (APPENV === 'dev') {
        # Injecte Vite client pour HMR + fichier JS
        return
            '<script type="module" src="' . getVite('local') . '/@vite/client"></script>' . PHP_EOL .
            '<script type="module" src="' . getVite('local') . '/' . $filePath . '"></script>' . PHP_EOL;
    }

    # PROD
    # ressources compilées dans public/assets
    $manifest = getVite('manifest');
    if (!isset($manifest[$filePath])) {
        throw new ServerException(__FUNCTION__ . ": Vite asset not found: $filePath");
    }
    $asset = $manifest[$filePath];

    return '<script type="module" src="/assets/' . $asset['file'] . '"></script>' . PHP_EOL;
}

/**
 * getVite retourne l'url de /public/hot ou un tableau php de manifest.json
 *
 * @param  string $local_or_manifest
 * @return mixed
 * 
 * @throws ServerException
 */
function getVite(string $local_or_manifest): mixed
{
    switch ($local_or_manifest) {
        case 'local':
            return 'http://localhost:5173';

        case 'manifest':
            $manifestPath = DIR_ROOT . '/public/assets/.vite/manifest.json';
            if (!file_exists($manifestPath)) {
                throw new ServerException(__FUNCTION__ . ': Vite manifest.json not found.');
            }
            return json_decode(file_get_contents($manifestPath), true);

        default:
            return null;
    }
}