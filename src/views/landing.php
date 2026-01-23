<?php
$title = 'Radaptor - Modern PHP Framework';
ob_start();
?>

<section class="hero min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Hero badge -->
                <div class="hero-badge mb-4">
                    <span class="badge-glow">
                        <i class="bi bi-stars me-2"></i>
                        Event-Driven PHP Framework
                    </span>
                </div>

                <!-- Hero title -->
                <h1 class="hero-title mb-4">
                    Build <span class="gradient-text">Powerful</span> Applications<br>
                    with <span class="gradient-text">Radaptor</span>
                </h1>

                <!-- Hero description -->
                <p class="hero-description mb-5">
                    A modern, event-based PHP framework designed for developers who demand
                    flexibility, performance, and elegant architecture.
                </p>

                <!-- CTA buttons -->
                <div class="d-flex gap-3 justify-content-center flex-wrap mb-5">
                    <a href="/auth/github" class="btn btn-primary btn-lg btn-glow">
                        <i class="bi bi-github me-2"></i>
                        Login with GitHub
                    </a>
                    <a href="https://github.com/radaptor" class="btn btn-outline-light btn-lg" target="_blank">
                        <i class="bi bi-book me-2"></i>
                        Documentation
                    </a>
                </div>

                <!-- Feature cards -->
                <div class="row g-4 mt-5">
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-lightning-charge"></i>
                            </div>
                            <h3 class="h5 mb-2">Event-Driven</h3>
                            <p class="text-muted mb-0">Every request maps to an event. Clean, predictable, testable.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-puzzle"></i>
                            </div>
                            <h3 class="h5 mb-2">Modular</h3>
                            <p class="text-muted mb-0">Organize by domain. Events, entities, forms, widgets per module.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h3 class="h5 mb-2">Type-Safe</h3>
                            <p class="text-muted mb-0">PHPStan level 9. Full IDE support. Catch bugs before runtime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
