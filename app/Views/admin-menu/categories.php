<?php
# app/Views/admin-menu/index.php
use function App\html;
 ?>
<h2>Catégories</h2>

<!-- modal -->
<div id="cant-delete-category" class="hidden">
    <p>Impossible de supprimer cette catégorie tant qu'elle contient des éléments.</p>
    <p>Éléments actuellement présents :</p>
    <div id="dishes-in-category"></div>
    <p>Veuillez les déplacer vers une autre catégorie ou les supprimer pour continuer.</p>
    <button type="button" class="close-container" data-container-id="cant-delete-category">OK</button>
</div>

<button type="button" class="open-container" data-container-id="category-form-container">Nouvelle catégorie.</button>
<div id="category-form-container" class="hidden">
    <form action="/admin/<?= $_SESSION['id'] ?>/creer/categorie" target="_self" method="POST" class="new-element-form" data-submit-button="submit-category">
        <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <label for="category-title">Titre: 
            <input type="text" id="category-title" name="title" required>
        </label>
        <button type="submit" id="submit-category" disabled>Valider</button>
        <button type="button" class="close-container" data-container-id="category-form-container">Annuler</button>
    </form>
</div>

<?php if (isset($categories) && !empty($categories)): ?>
    <form action="/admin/<?= html($_SESSION['id']) ?>/modifier/categorie" method="POST" target="_self">
        <input type="hidden"  name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <!-- modal -->
        <div id="delete-category" class="hidden">
            <p>Supprimer la catégorie <strong><span id="category-name"></span></strong> ?</p>
            <p>Cette action est <strong>définitive</strong>. La catégorie sera retirée de la carte du restaurant.</p>
            <button type="submit" id="delete-category-button" name="id" formaction="/admin/<?= $_SESSION['id'] ?>/supprimer/categorie">Supprimer</button>
            <button type="button" class="close-container" data-container-id="delete-category">Annuler</button>
        </div>
        <!-- liste des catégories -->
        <ul class="sortable" data-li-classname="category" data-save-button-id="save-category-order">
            <?php foreach ($categories as $row): ?>
            <li class="category" data-id="<?= html($row['id']) ?>">
                <span id="view-category-<?= html($row['id']) ?>" class="draggable">
                    <strong><?= html($row['title']) ?></strong>
                </span>
                <span id="edit-category-<?= html($row['id']) ?>" class="hidden">
                    <input type="text" name="title" value="<?= html($row['title']) ?>" size="30" class="category-<?= html($row['id']) ?>" maxlength="64" disabled>
                    <button type="submit" class="category-<?= html($row['id']) ?>" name="id" value="<?= html($row['id']) ?>" disabled>Valider</button>
                    <button type="button" class="category-<?= html($row['id']) ?> delete-category-button" data-id="<?= html($row['id']) ?>" data-title="<?= html($row['title']) ?>" disabled>Supprimer la catégorie</button>
                </span>
                <button type="button" class="modify-button" data-id="<?= html($row['id']) ?>" data-branch="category">Modifier</button>
            </li>
            <?php endforeach; ?>
        </ul>
    </form>
    <button type="button" id="save-category-order" class="save-list-order hidden" data-url="/update/categories-order" data-li-classname="category">Enregistrer l'ordre</button>
<?php else: ?>
    <p>Vous n'avez pas de catégories enregistrées pour le moment.</p>
<?php endif; ?>

