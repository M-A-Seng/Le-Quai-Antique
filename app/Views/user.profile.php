<?php 
use function App\html;
use function App\vite_js;
 ?>
<h1>Mon profil client</h1>

<?php if ($_SESSION['new_user']): ?>
    <div>
        <p>Bienvenue au Quai Antique !</p>
        <p>Compte créé avec succès ! Vous pouvez maintenant réserver votre table et plonger dans l'univers culinaire de la Savoie !</p>
    </div>
<?php endif; ?>

<h2>Réservations à venir</h2>

<?php if (!empty($reservations)): ?>
    <?php require DIR_ROOT . '/app/Views/components/reservationUpdateForm.php' ?>
    <?php require DIR_ROOT . '/app/Views/components/userReservationsDisplay.php' ?>

    <a href="/profil/<?= $_SESSION['id'] ?>/mes-reservations">Voir toutes mes réservations</a>
    <?= vite_js('resources/js/pages/user-profile.js') ?>
<?php elseif (!isset($error_message) || empty($error_message)): ?>
    <p>Vous n'avez pas de réservations prévues pour le moment!</p>
<?php endif; ?>

