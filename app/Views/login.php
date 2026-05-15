<?php 
use function App\html;
use function App\vite_js;
 ?>
<h1>Connexion</h1>

<form action="/connexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
    <label for="email">Email :
        <input id="email" name="email" type="email" required>
        <span id="email-feedback"></span>
    </label><br>
    <label for="password">Mot de passe :
        <input id="password" name="password" type="password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" required>
        <span id="password-feedback"></span>
    </label><br>

    <button type="submit" id="submit-button" disabled>Se connecter</button>
</form>

<p><a href="#">J'ai oublié mon mot de passe</a></p>
<p><a href="/inscription">Créer un compte</a></p>

<?= vite_js('resources/js/pages/login.js') ?>