<?php
use function App\html;
use function App\vite_js;
 ?>

<h1>Gestion de la carte</h1>

<div>
    <div>Plats</div>
    <div>Menu</div>
    <div>Catégories</div>
</div>

<div>
    <?php require_once DIR_ROOT . '/app/Views/components/categories.php' ?>
</div>

<?= vite_js('resources/js/pages/admin-menu/index.js') ?>