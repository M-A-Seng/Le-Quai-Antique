<?php

use function App\Helpers\cloudinary_img;
use function App\Helpers\html;
use function App\Helpers\vite_js;
 ?>

<style>
    /* Supprimer <style> une fois stylesheet établi */
    .gallery {
        margin: 0 9rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    .image {
        position: relative;
        width: 100%;
        height: 25rem;
        overflow: hidden;
    }
    .image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .caption {
        width: 100%;
        height: 40%;
        position: absolute;
        text-align: center;
        bottom: 0;
        color: white;
        background-color: rgba(0,0,0,0.5);
        z-index: 1;
    }
    #preview {
        width: 10rem;
        height: 10rem;
    }
</style>

<h1>Galerie</h1>

<?php if (isset($_SESSION['id']) && $_SESSION['role']->value === 'ADMIN'): ?>
    <button type="button" class="open-container" data-container-id="upload-form-container">Nouvelle image</button>
    <div id="upload-form-container" class="hidden">
        <form action="/admin/<?= $_SESSION['id'] ?>/importer/image" enctype="multipart/form-data" target="_self" method="POST" class="form-check-validity" data-submit-button="submit-image">
            <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
            
            <div id="delete-image" class="hidden">
                <p>Supprimer l'image <strong><span id="image-name"></span></strong> ?</p>
                <p>Cette action est <strong>définitive</strong>. L'image sera retiré de la galerie du restaurant.</p>
                <button type="submit" id="delete-image-button" name="id" value="" formaction="/admin/<?= $_SESSION['id'] ?>/supprimer/image">Supprimer</button>
                <button type="button" class="close-container" data-container-id="delete-image">Annuler</button>
            </div>
            
            <label for="imageTitle">Titre: 
                <input type="text" name="title" id="imageTitle" maxlength="128" required >
            </label>
            <label for="imageInput">Image: 
                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp" required >
                <span id="image-input-feedback"></span>
                <p><small>Sélectionnez une image .png, .jpeg ou .webp jusqu'à 10MB.</small></p>
            </label>
            <img id="preview" alt="Aperçu image">
            <button type="submit" id="submit-image" disabled>Valider</button>
            <button type="button" class="open-container hidden" id="open-warning-button" data-container-id="delete-image">Supprimer l'image</button>
            <button type="button" class="close-container" data-container-id="upload-form-container">Annuler</button>
        </form>
    </div>
<?php endif; ?>

<?php if (isset($images) && !empty($images)): ?>
<div class="gallery sortable" data-li-classname="image" data-save-button-id="save-images-order" <?= isset($_SESSION['id']) && $_SESSION['role']->value === 'ADMIN' ? 'data-formaction="/admin/'.$_SESSION['id'].'/modifier/image"' : '' ?>>

    <?php foreach ($images as $image): ?>
    <div class="image <?= isset($_SESSION['id']) && $_SESSION['role']->value === 'ADMIN' ? 'draggable' : '' ?>" data-id="<?= html($image['id']) ?>" data-public-id="<?= html($image['public_id']) ?>" data-slug="<?= html($image['slug']) ?>">
        <?= cloudinary_img($image['public_id'], ['width' => 1024, 'height' => 1024, 'widths' => [320, 480, 640, 800, 1024], 'alt' => 'IMAGE: '.$image['title'], 'id' => html('image-'.$image['id'])]) ?>
        <div class="caption">
            <?= html($image['title']) ?>
            <?php if (isset($_SESSION['id']) && $_SESSION['role']->value === 'ADMIN'): ?>
            <button type="button" class="modify-image open-container" data-container-id="upload-form-container" data-title="<?= html($image['title']) ?>" data-id="<?= html($image['id']) ?>">Modifier</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>
<button type="button" id="save-images-order" class="save-list-order hidden" data-url="/update/images-order" data-li-classname="image">Enregistrer l'ordre</button>
<?php else: ?>
<p>Aucune image disponible.</p>
<?php endif; ?>

<?= vite_js('resources/js/pages/gallery.js') ?>