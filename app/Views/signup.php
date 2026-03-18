<h1>Inscription</h1>

<?php if (isset($errorMessage) && !empty($errorMessage)): ?>
    <div style="color:red">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<form method="POST" action="/inscription" target="_self">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <label for="last_name">Nom* :
        <input id="last_name" name="last_name" type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$" required>
    </label><br>

    <label for="first_name">Prénom :
        <input id="first_name" name="first_name" type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$" placeholder="(Facultatif)">
    </label><br>

    <label for="email">Email* :
        <input id="email" name="email" type="email" required>
        <span id="email-message"></span>
    </label><br>

    <label for="tel">Téléphone :
        <input id="tel" name="tel" type="text" $pattern = "/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/"; placeholder="(Facultatif)">
    </label><br>

    <label for="password">Mot de passe* :
        <input id="password" name="password" type="password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" required>
        <span id="password-feedback"></span>
        <ul>
            <li id="password-lowercase">Minimum 1 lettre majuscule.</li>
            <li id="password-uppercase">Minimum 1 lettre minuscule.</li>
            <li id="password-number">Minimum 1 chiffre.</li>
            <li id="password-special-char">Minimum 1 caractères spécial.</li>
            <li id="password-length">Au moins 8 caractères.</li>
        </ul>
    </label><br>
    <label for="password-confirm">Confirmez votre mot de passe* :
        <input id="password-confirm" name="password-confirm" type="password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$" required>
        <span id="password-confirm-feedback"></span>
    </label><br>
    
    <label for="default_guest_count">Nombre de personnes habituel :
        <input id="default_guest_count" name="default_guest_count" type="number" min="1" max="20" value="1"><br>
        <small>Ce nombre sera utilisé pour préremplir vos futures réservations, vous pouvez le changer à tout moment dans votre espace client.</small>
    </label><br>
    
    <fieldset>
        <legend>Allergies alimentaires (Facultatif)</legend>
        <p>Souhaitez-vous spécifier des allergies alimentaires pour vous ou vos convives ? Vous pouvez ignorer si vous ne savez pas.</p><br>
        <small>Ces informations seront utilisées pour préremplir vos futures réservations, vous pouvez les changer à tout moment dans votre espace client.</small><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Lait">
            Lait
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Oeufs">
            Œufs
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Arachides">
            Arachides
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Fruits à coque">
            Fruits à coque
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Blé">
            Blé
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Crustacés">
            Crustacés
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Poisson">
            Poisson
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Soja">
            Soja
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Gluten">
            Gluten
        </label><br>
        <label>
            <input type="checkbox" id="other-allergy" value="Autres allergies">
            Autres allergies
            <input type="hidden" name="allergy[]" id="other-allergy-input" placeholder="Indiquez vos allergies ici !">
        </label><br>
        <label>
            <input type="checkbox" name="allergy[]" value="Pas d'allergies">
            Pas d'allergies
        </label><br>
    </fieldset>
    <button type="submit" id="submit-button" disabled>S'inscrire</button>
</form>

<p>Vous avez déjà un compte?</p>
<p><a href="/connexion">Se connecter</a></p>

<script src="/assets/js/signup.script.js" defer></script>