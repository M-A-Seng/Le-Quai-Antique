<?php 
use function App\html;
use function App\vite_js;
 ?>
<h1>Carte du restaurant</h1>

<div>
    <div>Plats à la carte</div>
    <div>Menus</div>
</div>

<?php if (isset($dishes) && !empty($dishes)): ?>
    <?php foreach ($dishes as $category => $rows): ?>

        <h2><?= html($category) ?></h2>
        <?php foreach ($rows as $dish): ?>
        <div>
            <div>
                <span><?= html($dish['title']) ?></span>
                <span><?= html($dish['price']) ?> €</span>
            </div>
            <div class="description">
                <?= html($dish['description']) ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($setmenus) && !empty($setmenus)): ?>
    <h2>Menus</h2>

    <?php foreach ($setmenus as $menu): ?>
        <div>
            <div><?= html($menu['title']) ?></div>
            <div class="description"><?= html($menu['description']) ?></div>
            <span><?= html($menu['price']) ?></span>
        </div>
    <?php endforeach; ?>
<?php endif; ?>