<?php 
use function App\html;
use function App\vite_js;
 ?>
<h1>Mes réservations</h1>
<button onclick="window.location.href='/reserver'">Nouvelle réservation</button>

<?php if (!empty($reservations)): ?>
    <?php require_once DIR_ROOT . '/app/Views/components/reservationUpdateForm.php' ?>
    <?php require_once DIR_ROOT . '/app/Views/components/userReservationsDisplay.php' ?>
    <?= vite_js('resources/js/pages/user-reservations.js') ?>
<?php elseif (!isset($error_message) || empty($error_message)): ?>
    <p>Vous n'avez aucune réservation enregistrée pour l'instant.</p>
<?php endif; ?>


