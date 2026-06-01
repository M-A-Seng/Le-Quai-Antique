<?php use function App\Helpers\html;
use function App\Helpers\vite_js;

 ?>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>[PREPROD] <?= html($head['title'] ?? 'Le Quai Antique - Restaurant') ?></title>

    <!-- navigateurs -->
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate, max-snippet:0, max-image-preview:none, max-video-preview:0" />
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, noimageindex" />
    <meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet" />
    <!-- empêcher indexation et cache -->
    <meta name="referrer" content="no-referrer" />

    <!-- DESIGN -->
    <link rel="icon" href="">
    <!-- MOBILE -->
    <meta name="theme-color" content="#ffffff">
    <!-- EXTRA DATA -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <meta name="cloudinary-cloud-name" content="<?= $_ENV['CLOUDINARY_CLOUD_NAME'] ?>">
</head>

<?php if (!isset($_SESSION['dev_token']) || empty($_SESSION['dev_token'])): ?>
    <div class="modal">
        <div class="modal-content">
            <p><strong>Access key required</strong></p>
            <form action="/access/preprod" method="POST" class="form-check-validity" data-submit-button="access-submit">
                <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
                <input type="password" name="access_key" maxlength="64" size="35" required>
                <button type="submit" id="access-submit" disabled>Submit</button>
            </form>
        </div>
    </div>
    <?= vite_js('resources/js/app.js') ?>
<?php else: ?>
    <?php require_once DIR_ROOT . '/app/Views/components/header.php' ?>

    <body>
        <?php require DIR_ROOT . '/app/Views/components/global-ui.php' ?>

            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php else: ?>
                <p>Le chargement du contenu a échoué.</p>
                <button onclick="location.reload()">Recharger la page</button>
                <button onclick="window.location.href='/'">Page d'accueil</button><br>
            <?php endif; ?>

    </body>

    <?php require_once DIR_ROOT . '/app/Views/components/footer.php' ?>
<?php endif; ?>