<?php
$title = 'Error - Radaptor';
ob_start();
?>

<section class="error-page min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="glass-card p-5">
                    <div class="error-icon mb-4">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h1 class="h3 mb-3">Oops! Something went wrong</h1>
                    <p class="text-muted mb-4">
                        <?= htmlspecialchars($error ?? 'An unexpected error occurred. Please try again.') ?>
                    </p>
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
