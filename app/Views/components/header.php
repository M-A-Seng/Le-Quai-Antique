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

