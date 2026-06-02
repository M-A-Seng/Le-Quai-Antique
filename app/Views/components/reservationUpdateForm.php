<?php
use function App\Helpers\html;
  ?>
<div id="cancel-warning" style="display:none">
    <button class="close-warning-button">✖</button>
    <p>Annuler la réservation du <span id="warning-datetime"></span> ?</p>
    <p>Cette action est <b>définitive</b>. Si besoin, vous pouvez modifier la date ou l'heure sans annuler.</p>
    <form id="cancel-form" action="" target="_self" method="POST">
        <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
        <input type="hidden" id="reservation_datetime" name="reservation_datetime" value="">
        <button type="submit" id="submit-cancel" name='id' value="">Annuler la réservation</button>
    </form>
    <button type="button" class="modify-button warning-modify-button close-warning-button" value="" data-datetime="">Modifier la réservation</button>
    <button type="button" class="close-warning-button">Garder la réservation</button>
</div>

<div id="form-container" style="display:none">
    <h2>Modification de la réservation</h2>
    <form action="/check/reservation" target="_self" method="POST" id="form">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>"><br>
        <input type="hidden" id="id" name="id" value=""><br>
        <input type="hidden" name="service_id" value=""><br>

        <div id="recap" style="display:none">
            <h2>Vérifiez vos modifications</h2>
            <button type="button" class="close-recap-button">✖</button>
            <p>Date : <span id="recap-date"></span></p>
            <p>Nom : <span id="recap-name"></span></p>
            <p>Téléphone : <span id="recap-tel"></span></p>
            <p>Invités : <span id="recap-guests"></span> personnes</p>
            <p>Allergies: <span id="recap-allergy"></span></p>
            <button type="submit" id="confirm-form-button" name='action' value='update' formaction="">Valider</button>
            <button type="button" class="close-recap-button">Modifier ma réservation</button>
        </div>

        <label for="reservation_date">Date de réservation* :
            <input id="reservation_date" name="reservation_date" type="date" value="" min="<?php echo new DateTime()->format('Y-m-d') ?>" required>
            <span id="date-feedback"></span>
        </label><br>

        <label for="reservation_time">Heure de réservation* :
            <input type="hidden" id="default_reservation_time" value=""><br>
            <select name="reservation_time" id="reservation_time" required></select>
        </label><br>

        <label for="guest_count">Nombre de personnes* :
            <input id="guest_count" name="guest_count" type="number" min="1" <?= $_SESSION['role']->value === 'ADMIN' ? '' : 'max="20"' ?> value="" required><br>
        </label><br>

        <label for="client_name">Nom* :
            <input id="client_name" name="client_name" type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$" value="" required>
        </label><br>

        <label for="client_tel">Tel: 
            <input id="client_tel" name="client_tel" type="text" $pattern = "/^(\+?[1-9]{1}[0-9\s\-]{6,15}|0[0-9\s\-]{6,15})$/" value="">
        </label><br>

        <fieldset>
            <legend>Allergies alimentaires : (Facultatif)</legend>
            <label>
                <input type="checkbox" name="allergy[]" value="Lait">
                Lait
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Oeufs">
                Œufs
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Arachides/Cacahuètes">
                Arachides/Cacahuètes
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Fruits à coque/Noix">
                Fruits à coque/Noix
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
                <input type="checkbox" name="allergy[]" value="Mollusques">
                Mollusques
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Céleri">
                Céleri
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
                <input type="checkbox" name="allergy[]" value="Moutarde">
                Moutarde
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Graines de sésame">
                Graines de sésame
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Lupin">
                Lupin
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Gluten">
                Gluten
            </label><br>
            <label>
                <input type="checkbox" name="allergy[]" value="Pas d'allergies">
                Pas d'allergies
            </label><br>
        </fieldset>

        <p id="form-feedback"></p>
        <button type="submit" id="submit-button" name="ajax" value="update">Valider</button>
        <button type="button" class="cancel-button" id="form-cancel-button" value="" data-datetime="">Annuler ma réservation</button>
        <button type="button" id="close-form-button">Fermer</button>
    </form>
</div>