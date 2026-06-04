<?php
use function App\Helpers\html;
use function App\Helpers\vite_js;
 ?>
<h1>Restaurant<br>Le Quai Antique</h1>

<h2>Service du midi</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= isset($LUNCH['id']) ? html($LUNCH['id']) : '' ?>">
    <input type="hidden" id="lunch_service_duration" value="<?= isset($LUNCH['service_duration']) ? html($LUNCH['service_duration']) : '' ?>">
    <input type="hidden" id="default_lunch_opening_time" value="<?= isset($LUNCH['opening_time']) ? html($LUNCH['opening_time']) : '' ?>">

    <label for="lunch_opening_time">Heure d'ouverture* :
        <select name="opening_time" id="lunch_opening_time"></select>
    </label><br>

    <label for="lunch_closing_time">Service du midi ferme à : 
        <input readonly type="time" name="closing_time" id="lunch_closing_time" value="<?= isset($LUNCH['closing_time']) ? html($LUNCH['closing_time']) : '' ?>" tabindex="-1">
    </label><br>

    <label for="lunch_max_guests">Nombre maximum de convives* :
        <input id="lunch_max_guests" name="max_guests" type="number" min="1" max="100000" value="<?= isset($LUNCH['max_guests']) ? html($LUNCH['max_guests']) : '' ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du midi.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<h2>Service du soir</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= isset($DINNER['id']) ? html($DINNER['id']) : '' ?>">
    <input type="hidden" id="dinner_service_duration" value="<?= isset($DINNER['service_duration']) ? html($DINNER['service_duration']) : '' ?>">
    <input type="hidden" id="default_dinner_opening_time" value="<?= isset($DINNER['opening_time']) ? html($DINNER['opening_time']) : '' ?>">

    <label for="dinner_opening_time">Heure d'ouverture* :
        <select name="opening_time" id="dinner_opening_time"></select>
    </label><br>

    <label for="dinner_closing_time">Service du soir ferme à : 
        <input readonly type="time" name="closing_time" id="dinner_closing_time" value="<?= isset($DINNER['closing_time']) ? html($DINNER['closing_time']) : '' ?>" tabindex="-1">
    </label><br>

    <label for="dinner_max_guests">Nombre maximum de convives* :
        <input id="dinner_max_guests" name="max_guests" type="number" min="1" max="100000" value="<?= isset($DINNER['max_guests']) ? html($DINNER['max_guests']) : '' ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du soir.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<?= vite_js('resources/js/pages/admin-services.js') ?>