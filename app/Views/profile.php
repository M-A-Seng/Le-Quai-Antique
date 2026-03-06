<h1>Mon profil client</h1>

<form action="/deconnexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit">Se déconnecter</button>
</form>