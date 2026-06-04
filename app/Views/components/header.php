<?php
use function App\Helpers\html;
  ?>
<p>HEADER</p>

<?php if (isset($_SESSION['id']) && isset($_SESSION['role'])): ?>
    <form action="/deconnexion" target="_self" method="POST">
        <input type="hidden" name="csrf_token" value="<?= html($_SESSION['csrf_token']) ?>">
        <button type="submit">Se déconnecter</button>
    </form>
<?php endif; ?>

<ul>
    <li><a href="/la-carte">La Carte</a></li>
    <li><a href="/galerie">Gallerie</a></li>
    <li><a href="/reserver">Réserver une table</a></li>

    <?php if (isset($_SESSION['role']) && $_SESSION['role']->value === 'CLIENT'): ?>
    <li><a href="/profil/<?= html($_SESSION['id']) ?>">Mon profil</a></li>
    <li><a href="/profil/<?= html($_SESSION['id']) ?>/mes-reservations">Mes réservations</a></li>

    <?php elseif (isset($_SESSION['role']) && $_SESSION['role']->value === 'ADMIN'): ?>
    <li><a href="/admin/<?= html($_SESSION['id']) ?>/parametres/services">Services</a></li>
    <li><a href="/admin/<?= html($_SESSION['id']) ?>/reservations">Réservations</a></li>
    <li><a href="/admin/<?= html($_SESSION['id']) ?>/gestion/plats">Plats à la carte</a></li>
    <li><a href="/admin/<?= html($_SESSION['id']) ?>/gestion/menus">Menus à la carte</a></li>
    <li><a href="/admin/<?= html($_SESSION['id']) ?>/gestion/categories">Catégories</a></li>

    <?php else: ?>
    <li><a href="/connexion">Se connecter</a></li>
    <li><a href="/inscription">S'inscrire</a></li>    
    <?php endif; ?>
</ul>

