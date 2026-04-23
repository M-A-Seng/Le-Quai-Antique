<?php use function App\html; ?>
<h1>Restaurant<br>Le Quai Antique</h1>

<h2>Service du midi</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= html($LUNCH['id']) ?>">
    <input type="hidden" id="lunch_service_duration" value="<?= html($LUNCH['service_duration']) ?>">
    <input type="hidden" id="default_lunch_opening_time" value="<?= html($LUNCH['opening_time']) ?>">

    <label for="lunch_opening_time">Heure d'ouverture* :
        <select name="opening_time" id="lunch_opening_time"></select>
    </label><br>

    <label for="lunch_closing_time">Service du midi ferme à : 
        <input readonly type="time" name="closing_time" id="lunch_closing_time" value="<?= html($LUNCH['closing_time']) ?>" tabindex="-1">
    </label><br>

    <label for="lunch_max_guests">Nombre maximum de convives* :
        <input id="lunch_max_guests" name="max_guests" type="number" min="1" max="100000" value="<?= html($LUNCH['max_guests']) ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du midi.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<h2>Service du soir</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id" value="<?= html($DINNER['id']) ?>">
    <input type="hidden" id="dinner_service_duration" value="<?= html($DINNER['service_duration']) ?>">
    <input type="hidden" id="default_dinner_opening_time" value="<?= html($DINNER['opening_time']) ?>">

    <label for="dinner_opening_time">Heure d'ouverture* :
        <select name="opening_time" id="dinner_opening_time"></select>
    </label><br>

    <label for="dinner_closing_time">Service du soir ferme à : 
        <input readonly type="time" name="closing_time" id="dinner_closing_time" value="<?= html($DINNER['closing_time']) ?>" tabindex="-1">
    </label><br>

    <label for="dinner_max_guests">Nombre maximum de convives* :
        <input id="dinner_max_guests" name="max_guests" type="number" min="1" max="100000" value="<?= html($DINNER['max_guests']) ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du soir.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<form action="/deconnexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
    <button type="submit">Se déconnecter</button>
</form>

<script src="/assets/js/services.script.js" defer></script>