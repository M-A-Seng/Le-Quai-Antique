<h1>Mon profil client</h1>

<?php if ($_SESSION['new_user']): ?>
    <div>
        <p>Bienvenue au Quai Antique !</p>
        <p>Compte créé avec succès ! Vous pouvez maintenant réservez votre table et plonger dans l'univers culinaire de la Savoie !</p>
    </div>
<?php endif; ?>

<form action="/deconnexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit">Se déconnecter</button>
</form>