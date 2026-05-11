<?php use function App\html; ?>
<?php foreach ($reservations as $reservation): ?>
    <div>
        <div class="">
        <p>Date : <?= html($reservation['date_fullformat']) ?>.</p>
        <p>Statut : <?= html($reservation['status']) ?>.</p>
        <p>Nom : <?= $reservation['client_name'] ?>.</p>
        <p>Tel : <?= isset($reservation['client_tel']) ? html($reservation['client_tel']) : '' ?>.</p>
        <p>Invités : <?= html($reservation['guest_count']) ?> personnes.</p>
        <p>Allergies : <?= isset($reservation['allergy']) ? html($reservation['allergy']) : '' ?>.</p>
        <small>Créé le <?= isset($reservation['created_at']) ? html($reservation['created_at']) : '' ?>.</small>
        <small>Modifié le <?= isset($reservation['updated_at']) ? html($reservation['updated_at']) : '' ?>.</small>
        <?php if ($reservation['status'] === 'CONFIRMÉ'): ?>
            <button type="button" class="modify-button" value="<?= html($reservation['id']) ?>" data-datetime="<?= html($reservation['date_fullformat']) ?>">Modifier</button>
            <button type="button" class="cancel-button" value="<?= html($reservation['id']) ?>" data-datetime="<?= html($reservation['date_fullformat']) ?>">Annuler</button>
        <?php endif; ?>
            <p id="reservation-feedback" style="display:none"></p>
    </div>
<?php endforeach; ?>