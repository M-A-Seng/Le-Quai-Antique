<?php
use function App\Helpers\get_valid_env;
use function App\Helpers\html;
use function App\Helpers\vite_css;
 ?>
 <!-- GLOBAL -->
<title><?= (APPENV === 'dev' || APP_PROTECTED === 'true' ? '[DEV] ' : '') . (html($head['title'] ?? 'Le Quai Antique - Restaurant')) ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="">
<link rel="canonical" href="<?= html($head['canonical'] ?? '') ?>">
<!-- extra -->
<meta name="theme-color" content="#ffffff"> <!-- mobile -->
<meta name="csrf-token" content="<?= html($_SESSION['csrf_token'] ?? 0) ?>">
<meta name="cloudinary-cloud-name" content="<?= html(get_valid_env('CLOUDINARY_CLOUD_NAME')) ?>">
<!-- google font -->
<!-- montserrat -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<!-- hind mysuru -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Mysuru:wght@300;400;500;600;700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<!-- scripts -->
<?= vite_css('resources/js/app.js') ?>