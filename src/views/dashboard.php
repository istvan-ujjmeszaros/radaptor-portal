<?php
$title = 'Dashboard - Radaptor';
ob_start();
?>

<section class="dashboard py-5">
    <div class="container">
        <!-- Welcome header -->
        <div class="row mb-5">
            <div class="col-lg-8">
                <h1 class="mb-2">Welcome, <?= htmlspecialchars($user['name'] ?? 'Developer') ?>!</h1>
                <p class="text-muted lead">Manage your Radaptor license and access documentation.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- License card -->
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">
                            <i class="bi bi-key-fill me-2 text-primary"></i>
                            Your License Key
                        </h2>
                        <span class="badge bg-primary badge-tier">
                            <?= htmlspecialchars(License::getTierName($user['tier'])) ?>
                        </span>
                    </div>

                    <div class="license-key-box mb-4">
                        <code id="licenseKey"><?= htmlspecialchars($user['license_key']) ?></code>
                        <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyLicenseKey()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Add this key to your Radaptor <code>config/ApplicationConfig.php</code> file:
                        <pre class="mt-2 mb-0"><code>public const string LICENSE_KEY = '<?= htmlspecialchars($user['license_key']) ?>';</code></pre>
                    </div>
                </div>
            </div>

            <!-- Tier info -->
            <div class="col-lg-4">
                <div class="glass-card p-4">
                    <h3 class="h5 mb-3">
                        <i class="bi bi-star-fill me-2 text-warning"></i>
                        Your Plan Features
                    </h3>
                    <ul class="list-unstyled mb-0">
                        <?php foreach (License::getTierFeatures($user['tier']) as $feature): ?>
                        <li class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <?= htmlspecialchars($feature) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($user['tier'] === 'evaluation'): ?>
                    <hr>
                    <p class="text-muted small mb-2">Need more features?</p>
                    <a href="#" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-arrow-up-circle me-2"></i>
                        Upgrade Plan
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick links -->
            <div class="col-12">
                <div class="glass-card p-4">
                    <h3 class="h5 mb-4">
                        <i class="bi bi-rocket-takeoff me-2 text-primary"></i>
                        Quick Links
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="https://github.com/radaptor" class="quick-link" target="_blank">
                                <i class="bi bi-github"></i>
                                <span>GitHub Repository</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="quick-link">
                                <i class="bi bi-book"></i>
                                <span>Documentation</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="quick-link">
                                <i class="bi bi-chat-dots"></i>
                                <span>Community Discord</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="quick-link">
                                <i class="bi bi-envelope"></i>
                                <span>Contact Support</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account info -->
            <div class="col-12">
                <div class="glass-card p-4">
                    <h3 class="h5 mb-3">
                        <i class="bi bi-person-circle me-2 text-primary"></i>
                        Account Information
                    </h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Email</p>
                            <p class="mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Member Since</p>
                            <p class="mb-0"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Last Login</p>
                            <p class="mb-0"><?= date('F j, Y g:i A', strtotime($user['last_login'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function copyLicenseKey() {
    const key = document.getElementById('licenseKey').textContent;
    navigator.clipboard.writeText(key).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 2000);
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
