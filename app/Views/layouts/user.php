<?php 
use function App\Helpers\html;
use function App\Helpers\vite_js;
 ?>
<html>
    <head>
        <?php require_once DIR_ROOT . '/app/Views/components/head/head.php' ?>
        <?php require_once DIR_ROOT . '/app/Views/components/head/head-private.php' ?>
    </head>

    LAYOUT USER  <!-- retirer en prod -->

    <?php require_once DIR_ROOT . '/app/Views/components/header.php' ?>

    <body>
        <?php require DIR_ROOT . '/app/Views/components/global-ui.php' ?>

        <?php if (isset($requireLogin) && $requireLogin): ?>
            <div>
                <p>Votre session a expiré. Veuillez vous reconnecter.</p>

                <button onclick="window.location.href='/connexion'">Se connecter</button>
                <button onclick="window.location.href='/'">Page d'accueil</button><br>
            </div>
        <?php endif; ?>

        <?php if (isset($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <p>Le chargement du contenu a échoué.</p>
            <button onclick="location.reload()">Recharger la page</button>
            <button onclick="window.location.href='/'">Page d'accueil</button><br>
        <?php endif; ?>
    </body>

    <?php require_once DIR_ROOT . '/app/Views/components/footer.php' ?>
</html>

