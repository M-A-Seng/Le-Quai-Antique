<?php
# app/Views/admin-menu/index.php
use function App\html;
 ?>
<h2>Menus</h2>

<button type="button" class="open-container" data-container-id="menu-form-container">Ajouter un menu</button>
<div id="menu-form-container" class="hidden">
    <form class="new-element-form" action="/admin/<?= $_SESSION['id'] ?>/creer/menu" target="_self" method="POST" data-submit-button="submit-menu">
        <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <label for="menu-title">Titre: 
            <input type="text" id="menu-title" name="title" maxlength="128" required>
        </label>
        <label for="menu-description">Description: 
            <textarea id="menu-description" name="description" cols="40" rows="10" required></textarea>
        </label>
        <label for="menu-price">Prix: 
            <input type="text" id="menu-price" size="10" maxlength="10" name="price" placeholder="0,00" pattern="^\d[\d\s]*(?:[.,]\d+)?$" required> € 
        </label>
        <button id="submit-menu" type="submit" disabled>Valider</button>
        <button type="button" class="close-container" data-container-id="menu-form-container">Annuler</button>
    </form>
</div>

<?php if (isset($setmenus) && !empty($setmenus)): ?>
    <form action="/admin/<?= html($_SESSION['id']) ?>/modifier/menu" method="POST" target="_self">
        <input type="hidden"  name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <!-- modal -->
        <div id="delete-menu" class="hidden">
            <p>Supprimer <strong><span id="menu-name"></span></strong> ?</p>
            <p>Cette action est <strong>définitive</strong>. Le menu sera retiré de la carte du restaurant.</p>
            <button type="submit" id="delete-menu-button" name="id" value="" formaction="/admin/<?= $_SESSION['id'] ?>/supprimer/menu">Supprimer</button>
            <button type="button" class="close-container" data-container-id="delete-menu">Annuler</button>
        </div>

        <!-- liste des catégories -->
        <ul class="sortable" data-li-classname="menu" data-save-button-id="save-menu-order">
            <?php foreach ($setmenus as $menu): ?>
            <li class="menu" data-id="<?= html($menu['id']) ?>">
                <span id="view-menu-<?= html($menu['id']) ?>" class="draggable">
                    <strong><?= html($menu['title']) ?></strong> <span class="description"><details><summary>Détails:</summary><?= html($menu['description']) ?></details></span> <span><?= html($menu['price']) ?> €</span>
                </span>
                <span id="edit-menu-<?= html($menu['id']) ?>" class="hidden">
                    <label>Nom: <input type="text" name="title" value="<?= html($menu['title']) ?>" maxlength="128" size="30" class="menu-<?= html($menu['id']) ?>" disabled></label>
                    <label>Description: <textarea name="description" cols="40" rows="10" class="menu-<?= html($menu['id']) ?> description" disabled><?= html($menu['description']) ?></textarea></label>
                    <label>Prix: <input type="text" name="price" value="<?= html($menu['price']) ?>" maxlength="10" size="10" class="menu-<?= html($menu['id']) ?>" pattern="^\d[\d\s]*(?:[.,]\d+)?$" disabled> € </label>| 
                    <button type="submit" name="id" value="<?= html($menu['id']) ?>" class="menu-<?= html($menu['id']) ?>" disabled>Valider</button>
                    <button type="button" 
                            data-title="<?= html($menu['title']) ?>" data-id="<?= html($menu['id']) ?>" 
                            data-delete-button-id="delete-menu-button" data-placeholder-id="menu-name" data-container-id="delete-menu" 
                            class="delete-button menu-<?= html($menu['id']) ?>" disabled>
                            Supprimer
                    </button>
                </span>
                <button type="button" class="modify-button" data-id="<?= html($menu['id']) ?>" data-branch="menu">Modifier</button>
            </li>
            <?php endforeach; ?>
        </ul>
    </form>
    <button type="button" id="save-menu-order" class="save-list-order hidden" data-url="/update/menus-order" data-li-classname="menu">Enregistrer l'ordre</button>
<?php else: ?>
    <p>Vous n'avez aucun menu à la carte pour le moment.</p>
<?php endif; ?>