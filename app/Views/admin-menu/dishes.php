<?php
# app/Views/admin-menu/index.php
use function App\html;
 ?>
<h2>Plats à la carte</h2>

<!-- nouveau plat -->
<button type="button" class="open-container" data-container-id="new-dish-container">Ajouter un plat</button>
<div id="new-dish-container" class="hidden">
    <form class="new-element-form" 
          data-submit-button="submit-dish" 
          action="/admin/<?= html($_SESSION['id']) ?>/creer/plat" method="POST" target="_self">

        <input type="hidden"  name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <label for="dish-title">Nom: <input type="text" id="dish-title" name="title" required></label>
        <label for="dish-description">Description: <input type="textarea" id="dish-description" name="description" required></label>
        <label for="dish-price">Prix: <input type="text" id="dish-price" name="price" placeholder="0,00" pattern="^\d[\d\s]*(?:[.,]\d+)?$" required> € </label>
        <label for="dish-category">Catégorie: 
            <?php if (isset($categories)): ?>
            <select name="category_id" id="dish-category">
                <?php foreach($categories as $row): ?>
                <option value="<?= html($row['id']) ?>">
                    <?= html($row['title']) ?>
                </option>
                <?php endforeach; ?>
                <option value="" selected>Non catégorisé</option>
            </select>
            <?php else: ?>
                <small><a href="/admin/<?= html($_SESSION['id']) ?>/gestion/categories">Créer une catégorie</a></small>
            <?php endif; ?>
        </label>
        <button type="submit" id="submit-dish" disabled>Valider</button>
        <button type="button" class="close-container" data-container-id="new-dish-container">Annuler</button>
    </form>
</div>

<?php if (isset($dishes) && !empty($dishes)): ?>
<form action="/admin/<?= html($_SESSION['id']) ?>/modifier/plat" method="POST" target="_self">
    <input type="hidden"  name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
    <!-- modal -->
    <div id="delete-dish" class="hidden">
        <p>Supprimer <strong><span id="dish-name"></span></strong> ?</p>
        <p>Cette action est <strong>définitive</strong>. L'assiette sera retirée de la carte du restaurant.</p>
        <button type="submit" id="delete-dish-button" name="id" formaction="/admin/<?= html($_SESSION['id']) ?>/supprimer/plat">Supprimer</button>
        <button type="button" class="close-container" data-container-id="delete-dish">Annuler</button>
    </div>

    <?php foreach($dishes as $category => $rows): ?>
    <!-- liste des plats -->
    <h3><?= html($category) ?></h3>
    <?php if ($category === 'Assiettes non catégorisées'): ?>
    <p>Ces assiettes ne sont pas affichées sur la carte du restaurant.</p>
    <?php endif; ?>
    <ul class="sortable" 
        data-li-classname="list-<?= empty($rows[0]['category_id']) ? 'x' : html($rows[0]['category_id']) ?>" 
        data-save-button-id="save-dish-order-<?= empty($rows[0]['category_id']) ? 'x' : html($rows[0]['category_id']) ?>">

        <?php foreach($rows as $row): ?>
        <li class="list-<?= empty($row['category_id']) ? 'x' : html($row['category_id']) ?>" 
            data-id="<?= html($row['id']) ?>">

            <span id="view-dish-<?= html($row['id']) ?>" class="draggable">
                <span><strong><?= html($row['title']) ?></strong>: <small><?= html($row['description']) ?></small></span> <span><?= html($row['price']) ?> €</span> 
            </span>

            <span id="edit-dish-<?= html($row['id']) ?>" class="hidden">
                <label>Nom: <input type="text" name="title" value="<?= html($row['title']) ?>" maxlength="128" size="30" class="dish-<?= html($row['id']) ?>" disabled></label>
                <label>Description: <textarea name="description" cols="40" rows="2" class="dish-<?= html($row['id']) ?>" disabled><?= html($row['description']) ?></textarea></label>
                <label>Prix: <input type="text" name="price" value="<?= html($row['price']) ?>" maxlength="10" size="10" class="dish-<?= html($row['id']) ?>" pattern="^\d[\d\s]*(?:[.,]\d+)?$" disabled> € </label>| 
                <label>Catégorie: 
                    <?php if (isset($categories)): ?>
                    <select name="category_id" class="dish-<?= html($row['id']) ?>" disabled>
                        <?php foreach($categories as $catgRow): ?>
                        <option value="<?= html($catgRow['id']) ?>" <?= $catgRow['id'] === $row['category_id'] ? 'selected' : '' ?>>
                            <?= html($catgRow['title']) ?>
                        </option>
                        <?php endforeach; ?>
                        <option value="" <?= empty($row['category_id']) ? 'selected' : '' ?>>Non catégorisé</option>
                    </select>
                    <?php else: ?>
                        <small><a href="/admin/<?= html($_SESSION['id']) ?>/gestion/categories">Créer une catégorie</a></small>
                    <?php endif; ?>
                </label>     
                <button type="submit" name="id" value="<?= html($row['id']) ?>" class="dish-<?= html($row['id']) ?>" disabled>Valider</button>
                <button type="button" 
                        data-title="<?= html($row['title']) ?>" data-id="<?= html($row['id']) ?>" 
                        data-delete-button-id="delete-dish-button" data-placeholder-id="dish-name" data-container-id="delete-dish" 
                        class="delete-button dish-<?= html($row['id']) ?>" disabled>
                        Supprimer
                </button>
            </span>
            <button type="button" class="modify-button" data-id="<?= html($row['id']) ?>" data-branch="dish">Modifier</button>
        </li>
        <?php endforeach; ?>
    </ul>
    <button type="button" 
            id="save-dish-order-<?= empty($rows[0]['category_id']) ? 'x' : html($rows[0]['category_id']) ?>" 
            class="save-list-order hidden" data-url="/update/dishes-order" 
            data-li-classname="list-<?= empty($rows[0]['category_id']) ? 'x' : html($rows[0]['category_id']) ?>">
            Enregistrer l'ordre
    </button>
    <?php endforeach; ?>
</form>
<?php else: ?>
    <p>Vous n'avez aucun plat à la carte pour le moment.</p>
<?php endif; ?>

