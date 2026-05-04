<?php use function App\html; ?>
LAYOUT

<script src="/assets/js/session-cleanup.js"></script>

<?php require_once __DIR__.'/../components/header.php' ?>

<body>
    <?php if (isset($error_message) && !empty($error_message)): ?>
        <div style="color:red">
            <?php echo html($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($confirmation_message) && !empty($confirmation_message)): ?>
        <div style="color:green">
            <?php echo html($confirmation_message); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</body>

<?php require_once __DIR__.'/../components/footer.php' ?>
