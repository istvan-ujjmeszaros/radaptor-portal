<?php
$title = 'Radaptor - Widget-Driven PHP Platform';
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
                        Widget-Driven UI Composition Platform
                    </span>
                </div>

                <!-- Hero title -->
                <h1 class="hero-title mb-4">
                    Build <span class="gradient-text">Widget-Driven</span> Applications<br>
                    with <span class="gradient-text">Radaptor</span>
                </h1>

                <!-- Hero description -->
                <p class="hero-description mb-5">
                    Radaptor combines explicit request handling with a renderer-agnostic composition model.
                    Widgets own their render contract, while the CMS aggregates the tree, resolves routes,
                    and renders HTML today.
                </p>

                <!-- CTA button -->
                <div class="mb-5">
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="https://github.com/radaptor" class="btn btn-outline-light btn-lg" target="_blank">
                            <i class="bi bi-book me-2"></i>
                            Documentation
                        </a>
                        <a href="/comparison" class="btn btn-primary btn-glow btn-lg">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Technical Comparison
                        </a>
                    </div>
                </div>

                <!-- Feature cards -->
                <div class="row g-4 mt-5">
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-lightning-charge"></i>
                            </div>
                            <h3 class="h5 mb-2">Explicit Request Flow</h3>
                            <p class="text-muted mb-0">Every request maps to one event handler. Authorization and execution stay visible.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-puzzle"></i>
                            </div>
                            <h3 class="h5 mb-2">Widget-Owned Contract</h3>
                            <p class="text-muted mb-0">Widgets return explicit tree nodes. The CMS composes pages without owning widget internals.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card glass-card p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h3 class="h5 mb-2">Type-Safe Tooling</h3>
                            <p class="text-muted mb-0">PHPStan level 9, full IDE support, and a composition model that stays understandable at scale.</p>
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
