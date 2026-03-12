<h1>Connexion</h1>

<form action="/connexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <p>
        <label for="email">Email :</label>
        <input id="email" name="email" type="email">
    </p>
    <p>
        <label for="password">Mot de passe :</label>
        <input id="password" name="password" type="password">
    </p>
    <p>
        <button type="submit">Se connecter</button>
    </p>
</form>

<?php if (isset($errorMessage)): ?>
    <div style="color:red">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<p><a href="#">J'ai oublié mon mot de passe</a></p>
<p><a href="/inscription">Créer un compte</a></p>