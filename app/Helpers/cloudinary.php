<?php

namespace App\Helpers;

function cloudinary_img(string $publicId, array $options = []): string 
{
    $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
    $publicId = trim($publicId);
    $publicId = ltrim($publicId, '/');

    // responsive
    $sizes = $options['sizes'] ?? '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw'; // mobile 1 col, tablet 2 col, desktop 3 col
    $widths = $options['widths'] ?? [320, 480, 768, 1024, 1280, 1600, 1920];

    // Transformations Cloudinary
    $baseTransform = implode(',', [
        'f_auto',   // format auto webp/avif
        'q_auto',   // qualité auto
        'c_limit',  // ne pas agrandir l'image au-delà de sa taille
        'w_{width}',
        'dpr_auto'  // adaptation auto aux appareils
    ]);

    // Génération srcset
    $srcset = [];
    foreach ($widths as $width) {
        $transform = str_replace('{width}', (string)$width, $baseTransform);
        $url = sprintf(
            'https://res.cloudinary.com/%s/image/upload/%s/%s',
            rawurlencode($cloudName),
            $transform,
            rawurlencode($publicId)
        );
        $srcset[] = "{$url} {$width}w";
    }

    // Image de secours (par défaut)
    $fallbackTransform = str_replace('{width}', '1280', $baseTransform);
    $src = sprintf(
        'https://res.cloudinary.com/%s/image/upload/%s/%s',
        rawurlencode($cloudName),
        $fallbackTransform,
        rawurlencode($publicId)
    );
    
    $attributes = [
        'src'           => $src,
        'srcset'        => implode(', ', $srcset),
        'sizes'         => $sizes,
        'alt'           => $options['alt'] ?? 'Image',
        'loading'       => 'lazy',
        'decoding'      => 'async',
        'fetchpriority' => $options['fetchpriority'] ?? 'auto',
        'width'         => (string)($options['width'] ?? 1200),
        'height'        => (string)($options['height'] ?? 800),
    ];
    // Ajouter class et id uniquement si défini et non vide
    if (!empty($options['class'])) {
        $attributes['class'] = $options['class'];
    }
    if (!empty($options['id'])) {
        $attributes['id'] = $options['id'];
    }
    
    $html = '';
    foreach ($attributes as $name => $value) {
        $html .= sprintf(' %s="%s"', $name, html((string)$value));
    }
    return '<img' . $html . '>';
}