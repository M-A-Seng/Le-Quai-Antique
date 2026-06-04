<?php
use function App\Helpers\get_valid_env;
use function App\Helpers\html;
 ?>
 <!-- GLOBAL -->
<title><?= (APPENV === 'dev' || APP_PROTECTED === 'true' ? '[DEV] ' : '') . (html($head['title'] ?? 'Le Quai Antique - Restaurant')) ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="">
<link rel="canonical" href="<?= html($head['canonical'] ?? '') ?>">
<!-- extra -->
<meta name="theme-color" content="#ffffff"> <!-- mobile -->
<meta name="csrf-token" content="<?= html($_SESSION['csrf_token']) ?>">
<meta name="cloudinary-cloud-name" content="<?= html(get_valid_env('CLOUDINARY_CLOUD_NAME')) ?>">