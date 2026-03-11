<?php
$title = 'Technical Comparison - Radaptor';
ob_start();
?>

<section class="hero py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-badge mb-4">
                    <span class="badge-glow">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Technical Positioning
                    </span>
                </div>

                <h1 class="hero-title mb-4">
                    Why <span class="gradient-text">Radaptor</span> Feels Different<br>
                    From Typical MVC Flow
                </h1>

                <p class="hero-description mb-5">
                    Radaptor treats both the use case and the UI contract as first-class objects.
                    One request resolves to one handler, and widgets return explicit tree nodes the CMS can compose and route.
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="https://github.com/radaptor" class="btn btn-outline-light btn-lg" target="_blank">
                        <i class="bi bi-book me-2"></i>
                        Read the README
                    </a>
                    <a href="/" class="btn btn-primary btn-glow btn-lg">
                        <i class="bi bi-house me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card glass-card p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h2 class="h5 mb-3">One Request, One Handler</h2>
                    <p class="text-muted mb-0">
                        Radaptor naturally encourages one concrete handler per actionable request instead
                        of growing multi-action controllers over time.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card glass-card p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h2 class="h5 mb-3">Authorization Stays Local</h2>
                    <p class="text-muted mb-0">
                        The authorization contract lives on the request handler itself, which makes it
                        easier to audit, debug, and reason about.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card glass-card p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h2 class="h5 mb-3">Less Hidden Control Flow</h2>
                    <p class="text-muted mb-0">
                        You spend less time tracing route config, middleware, policy layers, and framework
                        conventions before reaching the real use case.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card glass-card p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h2 class="h5 mb-3">SDUI-Ready Composition</h2>
                    <p class="text-muted mb-0">
                        The CMS already builds an explicit component tree. HTML is the current renderer,
                        but the composition model is suitable for additional renderers and client channels.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card glass-card p-4 h-100">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-feather"></i>
                    </div>
                    <h2 class="h5 mb-3">Less Verbose, Less Ceremony</h2>
                    <p class="text-muted mb-0">
                        Small handlers do not need much framework scaffolding. The request model stays compact,
                        readable, and close to the actual use case.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="glass-card p-4 p-lg-5">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-3">Comparison at a glance</h2>
                    <p class="text-muted mb-0">
                        The point is not that other frameworks cannot be used cleanly. The point is that
                        Radaptor makes the explicit style the default path.
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Concern</th>
                            <th scope="col">Typical MVC Framework Flow</th>
                            <th scope="col">Radaptor Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Request entry point</th>
                            <td>Route resolves to a controller method or callable.</td>
                            <td>URL resolves directly to one concrete Event handler.</td>
                        </tr>
                        <tr>
                            <th scope="row">Authorization</th>
                            <td>Often distributed across middleware, policies, voters, or helper methods.</td>
                            <td>Part of the Event contract, next to the use case itself.</td>
                        </tr>
                        <tr>
                            <th scope="row">Use-case boundary</th>
                            <td>Can drift into larger multi-action controllers.</td>
                            <td>Defaults toward one handler per actionable request.</td>
                        </tr>
                        <tr>
                            <th scope="row">Debugging path</th>
                            <td>Trace routing, middleware, auth layers, and controller conventions.</td>
                            <td>Open the handler and start there.</td>
                        </tr>
                        <tr>
                            <th scope="row">Architectural drift</th>
                            <td>Easy to accumulate non-local checks and fat entry points.</td>
                            <td>Local request handlers keep behavior easier to inspect.</td>
                        </tr>
                        <tr>
                            <th scope="row">Simple handler ceremony</th>
                            <td>Often includes more framework imports, routing metadata, and HTTP wrapper objects.</td>
                            <td>Small use cases can stay small and direct.</td>
                        </tr>
                        <tr>
                            <th scope="row">CMS and UI composition</th>
                            <td>CMS is usually a separate product or layer on top of the framework.</td>
                            <td>CMS composition is part of the framework model and already builds a component tree.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h2 class="h4 mb-3">Built for server-driven UI</h2>
                    <p class="text-muted mb-3">
                        Radaptor's CMS is not just a page editor. Each widget owns a simple render contract
                        of <code>template</code>, <code>props</code>, <code>strings</code>, and <code>slots</code>,
                        and the CMS assembles those widget trees into pages.
                    </p>
                    <p class="mb-0">
                        HTML is the current renderer, but the composition model is already decoupled from that output.
                        Additional renderers can be added without redesigning how content and layout are authored.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h2 class="h4 mb-3">A fair comparison</h2>
                    <p class="text-muted mb-3">
                        Laravel and Symfony can both be structured around single-action or invokable handlers.
                        Symfony in particular is flexible enough to support a very explicit architecture.
                    </p>
                    <p class="mb-0">
                        Radaptor's claim is narrower and stronger: it starts from that model, so you do not
                        need to impose it against the framework's default gravity.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h2 class="h4 mb-3">Why the term "Event"?</h2>
                    <p class="text-muted mb-3">
                        In Radaptor, an Event is not a pub/sub event or a domain event in the DDD sense.
                    </p>
                    <p class="mb-0">
                        It is the concrete request handler for one use case. If you prefer ADR-style language,
                        you can think of it as an action object with an explicit authorization contract.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="glass-card p-4 p-lg-5 text-center">
            <h2 class="mb-3">The practical advantage</h2>
            <p class="lead text-muted mb-4">
                Radaptor reduces architectural surprise by making request handling and authorization explicit.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="https://github.com/radaptor" class="btn btn-outline-light" target="_blank">
                    <i class="bi bi-github me-2"></i>
                    Explore the code
                </a>
                <a href="/auth/github" class="btn btn-primary btn-glow">
                    <i class="bi bi-arrow-right-circle me-2"></i>
                    Get access
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
