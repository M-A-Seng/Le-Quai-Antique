<?php
# admin.menu.php
use function App\html;
use function App\vite_js;
 ?>
<h2>Catégories</h2>

<!-- modal -->
<div id="cant-delete-category" style="display:none">
    <p>Impossible de supprimer cette catégorie tant qu'elle contient des éléments.</p>
    <p>Éléments actuellement présents :</p>
    <div id="dishes-in-category"></div>
    <p>Veuillez les déplacer vers une autre catégorie ou les supprimer pour continuer.</p>
    <button type="button" class="close-modal" data-modal-id="cant-delete-category">OK</button>
</div>

<button type="button" id="new-category-button">Nouvelle catégorie.</button>
<!-- modal -->
<div id="category-form-container" style="display:none">
    <form action="/admin/<?= $_SESSION['id'] ?>/creer/categorie" target="_self" method="POST">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <label for="title">Titre: 
            <input type="text" id="title" name="title">
        </label>
        <button type="submit">Valider</button>
        <button type="button" class="close-modal" data-modal-id="category-form-container">Annuler</button>
    </form>
</div>

<?php if (isset($categories)): ?>
    <form action="/admin/<?= html($_SESSION['id']) ?>/modifier/categorie" method="POST" target="_self">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <!-- modal -->
        <div id="delete-category" style="display:none">
            <p>Supprimer la catégorie <strong><span id="category-name"></span></strong> ?</p>
            <p>Cette action est <strong>définitive</strong>. La catégorie n'apparaîtra plus sur la carte du restaurant.</p>
            <button type="submit" id="delete-category-button" name="id" formaction="/admin/<?= $_SESSION['id'] ?>/supprimer/categorie">Supprimer</button>
            <button type="button" class="close-modal" data-modal-id="delete-category">Annuler</button>
        </div>
        <!-- liste des catégories -->
        <ul id="categories">
            <?php foreach ($categories as $row): ?>
            <li class="category" data-id="<?= html($row['id']) ?>">
                <span class="dragdrop"> ↕ </span>
                <input type="text" value="<?= html($row['title']) ?>" id="cat-<?= html($row['id']) ?>" readonly>
                <button type="button" class="category-button" data-id="<?= html($row['id']) ?>" data-title="<?= html($row['title']) ?>">Modifier</button>
                <button type="submit" class="cat-<?= html($row['id']) ?>" name="id" value="<?= html($row['id']) ?>" style="display:none">Valider</button>
                <button type="button" class="cat-<?= html($row['id']) ?> delete-category-button" data-id="<?= html($row['id']) ?>" data-title="<?= html($row['title']) ?>" style="display:none">Supprimer la catégorie</button>
            </li>
            <?php endforeach; ?>
        </ul>
    </form>
    <button type="button" id="save-category-order" style="display:none">Enregistrer l'ordre</button>
<?php else: ?>
    <p>Vous n'avez pas de catégories enregistrées pour le moment.</p>
<?php endif; ?>

