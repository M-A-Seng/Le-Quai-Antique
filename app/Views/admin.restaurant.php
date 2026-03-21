<h1>Restaurant<br>Le Quai Antique</h1>

<?php if (isset($errorMessage) && !empty($errorMessage)): ?>
    <div style="color:red">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<?php if (isset($confirmationMessage) && !empty($confirmationMessage)): ?>
    <div style="color:green">
        <?php echo htmlspecialchars($confirmationMessage); ?>
    </div>
<?php endif; ?>
<?php if (isset($errorMessage) && !empty($errorMessage)): ?>
    <div style="color:red">
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
<?php endif; ?>

<h2>Service du midi</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" id="default_lunch_opening_time" value="<?= $lunchOpeningTime ?>">

    <label for="lunch_opening_time">Heure d'ouverture* :
        <select name="lunch_opening_time" id="lunch_opening_time"></select>
    </label><br>

    <label for="lunch_closing_time">Service du midi ferme à : 
        <input readonly type="time" name="lunch_closing_time" id="lunch_closing_time" value="<?= $lunchClosingTime ?>" tabindex="-1">
    </label><br>

    <label for="lunch_max_guests">Nombre maximum de convives* :
        <input id="lunch_max_guests" name="lunch_max_guests" type="number" min="1" max="100000" value="<?= $lunchMaxGuests ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du midi.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<h2>Service du soir</h2>

<form action="" method="POST" target="_self">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" id="default_evening_opening_time" value="<?= $eveningOpeningTime ?>">

    <label for="evening_opening_time">Heure d'ouverture* :
        <select name="evening_opening_time" id="evening_opening_time"></select>
    </label><br>

    <label for="evening_closing_time">Service du soir ferme à : 
        <input readonly type="time" name="evening_closing_time" id="evening_closing_time" value="<?= $eveningClosingTime ?>" tabindex="-1">
    </label><br>

    <label for="evening_max_guests">Nombre maximum de convives* :
        <input id="evening_max_guests" name="evening_max_guests" type="number" min="1" max="100000" value="<?= $eveningMaxGuests ?>" required><br>
        <small>Définissez la capacité maximale du restaurant pour le service du soir.</small>
    </label><br>

    <button type="submit">Enregistrer</button>
</form>

<form action="/deconnexion" target="_self" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit">Se déconnecter</button>
</form>

<script src="/assets/js/restaurant.script.js" defer></script>