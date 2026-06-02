<?php
use function App\Helpers\html;
?>
<!-- MINIATURES URL -->
<!-- Open Graph  -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= html($head['title'] ?? 'Le Quai Antique - Restaurant') ?>">
<meta property="og:description" content="<?= html($head['description'] ?? "Offrez-vous un moment d'exception à Chambéry. Notre restaurant gastronomique en Savoie vous accueille pour une expérience raffinée. Réservez votre table et savourez une cuisine créative sublimant les produits locaux.") ?>">
<meta property="og:url" content="">
<meta property="og:image" content="">
<!-- Twitter/X -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= html($head['title'] ?? 'Le Quai Antique - Restaurant') ?>">
<meta name="twitter:description" content="<?= html($head['description'] ?? "Offrez-vous un moment d'exception à Chambéry. Notre restaurant gastronomique en Savoie vous accueille pour une expérience raffinée. Réservez votre table et savourez une cuisine créative sublimant les produits locaux.") ?>">
<meta name="twitter:image" content="">
<!-- SEO -->
<meta name="description" content="<?= html($head['description'] ?? "Offrez-vous un moment d'exception à Chambéry. Notre restaurant gastronomique en Savoie vous accueille pour une expérience raffinée. Réservez votre table et savourez une cuisine créative sublimant les produits locaux.") ?>">
<meta name="robots" content="index,follow">