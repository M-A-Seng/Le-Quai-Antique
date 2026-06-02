<?php
use function App\Helpers\vite_js;
 ?>
<h1>Gestion de la carte</h1>

<div>
    <div class="branch-button clickable" data-branch-id="branch-dishes" data-pathname="/admin/<?= $_SESSION['id'] ?>/gestion/plats">Plats</div>
    <div class="branch-button clickable" data-branch-id="branch-setmenus" data-pathname="/admin/<?= $_SESSION['id'] ?>/gestion/menus">Menu</div>
    <div class="branch-button clickable" data-branch-id="branch-categories" data-pathname="/admin/<?= $_SESSION['id'] ?>/gestion/categories">Catégories</div>
</div>

<div id="branch-dishes" class="branch <?= isset($default) && $default === 'dishes' ? '' : 'hidden' ?>">
    <?php require_once DIR_ROOT . '/app/Views/admin-menu/dishes.php' ?>
</div>

<div id="branch-setmenus" class="branch <?= isset($default) && $default === 'setmenus' ? '' : 'hidden' ?>">
    <?php require_once DIR_ROOT . '/app/Views/admin-menu/setmenus.php' ?>
</div>

<div id="branch-categories" class="branch <?= isset($default) && $default === 'categories' ? '' : 'hidden' ?>">
    <?php require_once DIR_ROOT . '/app/Views/admin-menu/categories.php' ?>
</div>

<?= vite_js('resources/js/pages/admin-menu/index.js') ?>