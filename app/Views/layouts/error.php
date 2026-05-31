LAYOUT ERROR PAGE

<head>
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <meta name="cloudinary-cloud-name" content="<?= $_ENV['CLOUDINARY_CLOUD_NAME'] ?>">
</head>

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
