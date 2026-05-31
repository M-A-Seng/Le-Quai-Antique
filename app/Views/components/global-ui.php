<?php
use function App\Helpers\html;
use function App\Helpers\vite_js;
 ?>
<?= vite_js('resources/js/app.js') ?>

<noscript>
    <div>
        <p>JavaScript est désactivé.</p>
        <p>Ce site utilise JavaScript pour fonctionner correctement. Activez-le dans les paramètres de votre navigateur puis rechargez la page pour continuer.</p>
        <a href=".">Recharger la page</a>
    </div>
</noscript>

<?php if (isset($error_message) && !empty($error_message)): ?>
    <div style="color:red">
        <?= html($error_message) ?>
    </div>
<?php endif; ?>

<?php if (isset($confirmation_message) && !empty($confirmation_message)): ?>
    <div style="color:green">
        <?= html($confirmation_message) ?>
    </div>
<?php endif; ?>
