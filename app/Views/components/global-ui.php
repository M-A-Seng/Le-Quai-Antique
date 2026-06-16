<?php
use function App\Helpers\html;
use function App\Helpers\vite_js;
 ?>
<?= vite_js('resources/js/app.js') ?>

<noscript>
    <div class="modal">
        <div class="modal_content">
            <p>JavaScript est désactivé.</p>
            <p>Ce site utilise JavaScript pour fonctionner correctement. Activez-le dans les paramètres de votre navigateur puis rechargez la page pour continuer.</p>
            <a href=".">Recharger la page</a>
        </div>
    </div>
</noscript>

<?php if (isset($error_message) && !empty($error_message)): ?>
    <div class="notif notif_error" id="error-notification">
        <button type="button" aria-label="Fermer la notification" class="close-container close-notif" data-container-id="error-notification"> ✖ </button>
        <?= html($error_message) ?>
    </div>
<?php endif; ?>

<?php if (isset($confirmation_message) && !empty($confirmation_message)): ?>
    <div class="notif notif_confirm" id="confirmation-notification">
        <button type="button" aria-label="Fermer la notification" class="close-container close-notif" data-container-id="confirmation-notification"> ✖ </button>
        <?= html($confirmation_message) ?>
    </div>
<?php endif; ?>
