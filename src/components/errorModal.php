<?php if (!empty($flashErrors)): ?>

    <div class="error-modal" id="errorModal">

        <div class="error-modal-content">

            <a href="#" class="error-modal-close">X</a>
            <h2>O-oh, Something went wrong</h2>

            <?php foreach ($flashErrors as $error): ?>

                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>