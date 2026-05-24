<?php 
use function App\html;
use function App\vite_js;
 ?>

<h1><?= isset($recap['display']) && $recap['display'] ? 'Vérifiez votre réservation' : 'Réserver une table' ?></h1>

<?php if (!isset($_SESSION['id']) || !isset($_SESSION['role'])): ?>
    <div id="require-login-modal" style="display:none">
        <button class="close-modal">✖</button>
        <p>Presque terminé ! Connectez-vous ou inscrivez-vous pour valider votre réservation.</p>
        
        <button onclick="window.location.href='/connexion'">Se connecter</button>
        <button onclick="window.location.href='/inscription'">S'inscrire</button><br>
        <button class="close-modal">Rester sur cette page</button>
    </div>

    <p>Pas encore connecté ? Connectez-vous pour une réservation plus rapide et simplifiée !</p>
    <a href="/connexion">Se connecter</a>
    <a href="/inscription">S'inscrire</a>
<?php endif; ?>

<form action="/check/reservation" target="_self" method="POST" id="form">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>

    <?php if (isset($_SESSION['id']) && isset($_SESSION['role'])): ?>
        <div id="recap" style="<?= isset($recap['display']) && $recap['display'] ? 'display:block' : 'display:none' ?>">
            <p>Date : <span id="recap-date"><?= isset($recap) ? $recap['date'] : '' ?></span></p>
            <p>Nom : <span id="recap-name"><?= isset($recap) ? $recap['name'] : '' ?></span></p>
            <p>Téléphone : <span id="recap-tel"><?= isset($recap) ? $recap['tel'] : '' ?></span></p>
            <p>Invités : <span id="recap-guests"><?= isset($recap) ? $recap['guest'] : '' ?></span> personnes</p>
            <p>Allergies: <span id="recap-allergy"><?= isset($recap) ? $recap['allergy'] : '' ?></span></p>
            <button type="submit" id="confirm-form-button" formaction="<?= isset($recap['formaction']) ? $recap['formaction'] : '' ?>" name="action" value="reserve">Valider</button>
            <button type="button" class="close-recap-button">Modifier ma réservation</button>
        </div>
    <?php endif; ?>

    <div id="form-fields" style="<?= isset($recap['display']) && $recap['display'] ? 'display:none' : 'display:block' ?>">
        <label for="reservation_date">Date de réservation* :
            <input id="reservation_date" name="reservation_date" type="date" 
                value="<?= isset($reservation_date) ? html($reservation_date) : '' ?>"
                min="<?php echo new DateTime()->format('Y-m-d') ?>" required>
            <span id="date-feedback"></span>
        </label><br>

        <label for="reservation_time">Heure de réservation* :
            <input type="hidden" id="default_reservation_time" value="<?= isset($reservation_time) ? html($reservation_time) : '' ?>"><br>
            <select name="reservation_time" id="reservation_time" required disabled></select>
        </label><br>

        <label for="guest_count">Pour combien de personnes ?* :
            <input id="guest_count" name="guest_count" type="number" min="1" <?= isset($_SESSION['role']) && $_SESSION['role']->value === 'ADMIN' ? '' : 'max="20"' ?> 
                value="<?= isset($guest_count) ? html($guest_count) : '' ?>" required disabled><br>
            <small>Pour une réservation supérieure à 20 personnes, veuillez appeler au </small>
        </label><br>

        <label for="client_name">À quel nom réservez-vous ?* :
            <input id="client_name" name="client_name" type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$" 
                value="<?= isset($client_name) ? html($client_name) : '' ?>" required>
        </label><br>

        <label for="client_tel">Un numéro de téléphone ? (facultatif):
            <input id="client_tel" name="client_tel" type="text" $pattern = "/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/"  
                value="<?= isset($client_tel) ? html($client_tel) : '' ?>">
        </label><br>

        <fieldset>
            <legend>Souhaitez-vous mentionner des allergies alimentaires ? (Facultatif)</legend>
            <label>
                <input type="checkbox" name="allergy[]" value="Lait" <?php if(isset($allergy)){echo in_array('Lait', $allergy) ? 'checked':'';} ?>>
                Lait
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Oeufs" <?php if(isset($allergy)){echo in_array('Oeufs', $allergy) ? 'checked':'';} ?>>
                Œufs
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Arachides/Cacahuètes" <?php if(isset($allergy)){echo in_array('Arachides/Cacahuètes', $allergy) ? 'checked' :'';} ?>>
                Arachides/Cacahuètes
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Fruits à coque/Noix" <?php if(isset($allergy)){echo in_array('Fruits à coque/Noix', $allergy) ? 'checked' :'';} ?>>
                Fruits à coque/Noix
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Blé" <?php if(isset($allergy)){echo in_array('Blé', $allergy) ? 'checked' :'';} ?>>
                Blé
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Crustacés" <?php if(isset($allergy)){echo in_array('Crustacés', $allergy) ? 'checked' :'';} ?>>
                Crustacés
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Mollusques" <?php if(isset($allergy)){echo in_array('Mollusques', $allergy) ? 'checked' :'';} ?>>
                Mollusques
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Céleri" <?php if(isset($allergy)){echo in_array('Céleri', $allergy) ? 'checked' :'';} ?>>
                Céleri
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Poisson" <?php if(isset($allergy)){echo in_array('Poisson', $allergy) ? 'checked' :'';} ?>>
                Poisson
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Soja" <?php if(isset($allergy)){echo in_array('Soja', $allergy) ? 'checked' :'';} ?>>
                Soja
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Moutarde" <?php if(isset($allergy)){echo in_array('Moutarde', $allergy) ? 'checked' :'';} ?>>
                Moutarde
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Graines de sésame" <?php if(isset($allergy)){echo in_array('Graines de sésame', $allergy) ? 'checked' :'';} ?>>
                Graines de sésame
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Lupin" <?php if(isset($allergy)){echo in_array('Lupin', $allergy) ? 'checked' :'';} ?>>
                Lupin
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Gluten" <?php if(isset($allergy)){echo in_array('Gluten', $allergy) ? 'checked' :'';} ?>>
                Gluten
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Pas d'allergies" <?php if(isset($allergy)){echo in_array("Pas d'allergies", $allergy) ? 'checked' :'';} ?>>
                Pas d'allergies
            </label><br>
        </fieldset>

        <p id="form-feedback"></p>
        <?php if (!isset($_SESSION['id']) || !isset($_SESSION['role'])): ?>
            <button id="submit-button" class="display-require-login-button" disabled>Réserver</button>
        <?php else: ?>
            <button type="submit" id="submit-button" name="ajax" disabled>Réserver</button>
        <?php endif; ?>
    </div>
</form>

<?= vite_js('resources/js/pages/reserve.js') ?>