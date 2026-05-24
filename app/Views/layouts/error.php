<?php 
    use function App\html;
    use function App\vite_js;
?>
LAYOUT ERROR PAGE

<head>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
</head>

<?php require_once DIR_ROOT . '/app/Views/components/header.php' ?>

<body>
    <noscript>
        <div>
            <p>JavaScript est désactivé.</p>
            <p>Ce site utilise JavaScript pour fonctionner correctement. Activez-le dans les paramètres de votre navigateur puis rechargez la page pour continuer.</p>
            <a href=".">Recharger la page</a>
        </div>
    </noscript>
    
    <?php if (isset($content)): ?>
        <?= $content ?>
    <?php else: ?>
        <p>Le chargement du contenu a échoué.</p>
        <button onclick="location.reload()">Recharger la page</button>
        <button onclick="window.location.href='/'">Page d'accueil</button><br>
    <?php endif; ?>

    <?= vite_js('resources/js/app.js') ?>
</body>

<?php require_once DIR_ROOT . '/app/Views/components/footer.php' ?>
