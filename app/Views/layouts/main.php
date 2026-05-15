<?php 
    use function App\html;
    use function App\vite_js;
?>
<p>DEFAULT LAYOUT</p>

<?php require_once DIR_ROOT . '/app/Views/components/header.php' ?>

<body>
    <?php if (isset($error_message) && !empty($error_message)): ?>
        <div style="color:red">
            <?php echo html($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($confirmation_message) && !empty($confirmation_message)): ?>
        <div style="color:green">
            <?php echo html($confirmation_message); ?>
        </div>
    <?php endif; ?>

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
