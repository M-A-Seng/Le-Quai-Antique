<?php use function App\html; ?>

<div id="require-login-modal" style="<?= isset($requireLogin) && $requireLogin ? 'display:block' : 'display:none' ?>">
    <button class="close-modal">✖</button>
    <p>Presque terminé ! Connectez-vous ou inscrivez-vous pour valider votre réservation.</p>
    
    <button onclick="window.location.href='/connexion'">Se connecter</button>
    <button onclick="window.location.href='/inscription'">S'inscrire</button><br>
    <button class="close-modal">Rester sur cette page</button>
</div>

<h1><?= !isset($recap['display']) || !$recap['display'] ? 'Réserver une table' : 'Vérifiez votre réservation' ?></h1>

<?php if (!isset($_SESSION['id']) || !isset($_SESSION['role'])): ?>
    <p>Pas encore connecté ? Connectez-vous pour une réservation plus rapide et simplifiée !</p>
    <a href="/connexion">Se connecter</a>
    <a href="/inscription">S'inscrire</a>
<?php endif; ?>

<form action="/check/reservation" target="_self" method="POST">
    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>

    <?php if (isset($recap['display']) && $recap['display']): ?>
        <div>
            <p>Date : <?= isset($recap['date']) ? html($recap['date']) : '' ?></p>
            <p>Nom : <?= isset($client_name) ? html($client_name) : '' ?></p>
            <p>Téléphone : <?= isset($client_tel) ? html($client_tel) : '' ?></p>
            <p>Invités : <?= isset($guest_count) ? html($guest_count) : '' ?> personnes</p>
            <p>Allergies: <?= isset($recap['allergy_string']) ? html($recap['allergy_string']) : '' ?></p>
            <button type="submit" formaction="/reserver">Valider</button>
            <a href='/reserver'>✎ Modifier ma réservation</a>
        </div>
    <?php endif; ?>

    <div style="<?= isset($recap['display']) && $recap['display'] ? 'display:none' : 'display:block' ?>">
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
            <input id="guest_count" name="guest_count" type="number" min="1" max="20" 
                value="<?= isset($guest_count) ? html($guest_count) : '' ?>" required disabled><br>
        </label><br>

        <label for="client_name">À quel nom réservez-vous ?* :
            <input id="client_name" name="client_name" type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$" 
                value="<?= isset($client_name) ? html($client_name) : '' ?>" required>
        </label><br>

        <label for="client_tel">Un numéro de téléphone ? (facultatif):
            <input id="client_tel" name="client_tel" type="text" $pattern = "/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/"; 
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

        <p id="feedback"></p>
        <button type="submit" id="submit-button" disabled>Réserver</button>
    </div>
</form>

<script src="/assets/js/reservation.script.js" defer></script>