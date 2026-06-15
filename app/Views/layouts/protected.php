<?php 
    use function App\Helpers\html;
    use function App\Helpers\vite_js;
?>
<html>
    <head>
        <?php require_once DIR_ROOT . '/app/Views/components/head/head.php' ?>
        <?php require_once DIR_ROOT . '/app/Views/components/head/head-private.php' ?>
    </head>

    LAYOUT PROTECTED  <!-- retirer en prod -->

    <?php if (!isset($_SESSION['dev_token']) || empty($_SESSION['dev_token'])): ?>
        <div class="modal">
            <div class="modal_content">
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
        <body data-page="<?= isset($page) ? html($page) : '' ?>">
            <?php require_once DIR_ROOT . '/app/Views/components/header.php' ?>
            <main>
                <?php require DIR_ROOT . '/app/Views/components/global-ui.php' ?>
                <?php if (isset($content)): ?>
                    <?= $content ?>
                <?php else: ?>
                    <p>Le chargement du contenu a échoué.</p>
                    <button onclick="location.reload()">Recharger la page</button>
                    <button onclick="window.location.href='/'">Page d'accueil</button><br>
                <?php endif; ?>
            </main>
            <?php require_once DIR_ROOT . '/app/Views/components/footer.php' ?>
        </body>
    <?php endif; ?>
</html>
