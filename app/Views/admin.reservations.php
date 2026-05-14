<?php use function App\html; ?>
<style>
    .hidden {
        display:none;
    }
    h2 {
        display:inline;
    }
</style>

<h1>Réservations</h1>

<div>
    <a href="/admin/<?= $_SESSION['id'] ?>/reservations/<?= $day_before ?? '' ?>">↩ Jour précédent </a>
    <h2><?= isset($today) && $today ? "Aujourd'hui" : (isset($french_formated_date) ? $french_formated_date : '') ?></h2>
    <a href="/admin/<?= $_SESSION['id'] ?>/reservations/<?= $day_after ?? '' ?>"> Jour suivant ↪</a>
    <?php if (isset($today) && !$today): ?>
        <p><a href="/admin/<?= $_SESSION['id'] ?>/reservations">Revenir à la date d'aujourd'hui</a></p>
    <?php endif; ?>
</div>

<button type="button" onclick="window.location.href='/reserver'">Nouvelle réservation</button>

<form id="search-form" action="/admin/<?= $_SESSION['id'] ?>/reservations" method="GET">
    <label for="date-search">Date: 
        <input type="date" id="date-search" data-user-id="<?= $_SESSION['id'] ?>" require>
    </label>
    <button type="submit">Valider</button>
</form>

<?php if (isset($reservations) && !empty($reservations)): ?>
    <div id="reservations-container" style="width:90%;">

        <div>
            <?php foreach ($service_types as $serviceEng => $serviceFr): ?>
            <div class="service" data-service="<?= html($serviceEng) ?>" data-capacity="">
                <h3><?= html($serviceFr) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($reservations as $service => $reservation): ?>
            <p id="<?= html($service) ?>-capacity" class="capacity-info <?= $display_by_default === $service ? '' : 'hidden' ?>">
                Capacité disponible: 
                <span class="remaining-places" data-service-id="<?= html($reservation[0]['service_id']) ?>">
                    <?= isset($remaining_places[$service]) ? html($remaining_places[$service]) : '?' ?>
                </span>
                 / <?= isset($max_guests[$service]) ? html($max_guests[$service]) : '?' ?>
            </p>

            <table id="<?= html($service) ?>" class="reservations-table <?= $display_by_default === $service ? '' : 'hidden' ?>">
                <colgroup>
                    <col style="width:15%">
                    <col style="width:15%">
                    <col style="width:35%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:15%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Nom</th>
                        <th>Convives</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>

                <?php foreach ($reservation as $row): ?>
                <tbody class="reservation">
                    <!-- LIGNE PRINCIPALE -->
                    <tr>
                        <td><?= html($row['reservation_date']) ?></td>
                        <td><?= html($row['reservation_time']) ?></td>
                        <td><?= html($row['client_name']) ?></td>
                        <td><?= html($row['guest_count']) ?></td>
                        <td><?= html($row['status']) ?></td>
                        <td><button class="detail-button" data-id="<?= html($row['id']) ?>">✎ Détails</button></td>
                    </tr>
                    <!-- ALLERGIES -->
                    <?php if (!empty($row['allergy'])): ?>
                    <tr class="allergy-row">
                        <td></td>
                        <td colspan="5"><small>Allergies: <?= html($row['allergy']) ?></small></td>
                    </tr>
                    <?php endif; ?>
                    <!-- DETAILS -->
                    <tr id="<?= html($row['id']) ?>-details" style="display:none">
                        <td colspan="5">
                            <span>Tel: <?= html($row['client_tel']) ?></span>
                            <small>Créé le: <?= html($row['created_at']) ?></small>
                            <small>Modifié le: <?= html($row['updated_at']) ?></small>
                            <?php if ($row['status'] === 'CONFIRMÉ'): ?>
                                <button type="button" class="modify-button" value="<?= html($row['id']) ?>" data-datetime="<?= html($row['date_fullformat']) ?>">Modifier</button>
                                <button type="button" class="cancel-button" value="<?= html($row['id']) ?>" data-datetime="<?= html($row['date_fullformat']) ?>">Annuler</button>
                            <?php endif; ?>
                            <p id="reservation-feedback" style="display:none"></p>
                        </td>
                    </tr>
                </tbody>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    </div>
    <?php require_once DIR_ROOT . '/app/Views/components/reservationUpdateForm.php' ?>
    <script src="/assets/js/reservation.form.script.js" defer></script>
    <script src="/assets/js/user.reservation.script.js" defer></script>
<?php elseif (!isset($error_message) || empty($error_message)): ?>
    <p>Aucune réservation enregistrée pour ce jour.</p>
<?php endif; ?>

<script src="/assets/js/admin.reservation.script.js" defer></script>