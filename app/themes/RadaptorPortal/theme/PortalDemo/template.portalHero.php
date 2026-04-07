<?php assert(isset($this) && $this instanceof Template); ?>
<?php
$comparison_url = (string) ($this->props['comparisonUrl'] ?? '/comparison/');
$request_access_url = (string) ($this->props['requestAccessUrl'] ?? '/request-access/');
$login_url = (string) ($this->props['loginUrl'] ?? '/login.html');
?>
<section class="hero min-vh-100 d-flex align-items-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-9 text-center">
				<div class="hero-badge mb-4">
					<span class="badge-glow">
						<i class="bi bi-stars me-2"></i>
						Widget-driven UI composition platform
					</span>
				</div>

				<h1 class="hero-title mb-4">
					Build <span class="gradient-text">widget-driven</span> applications<br>
					with <span class="gradient-text">Radaptor</span>
				</h1>

				<p class="hero-description mb-5">
					Radaptor combines explicit request handling with a renderer-agnostic composition model.
					Widgets own their render contract, while the CMS aggregates the component tree, resolves routes,
					and renders HTML today.
				</p>

				<div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-4">
					<a href="<?= e($comparison_url) ?>" class="btn btn-primary btn-glow btn-lg">
						<i class="bi bi-diagram-3 me-2"></i>
						Technical Comparison
					</a>
					<a href="<?= e($request_access_url) ?>" class="btn btn-outline-light btn-lg">
						<i class="bi bi-arrow-right-circle me-2"></i>
						Request Access
					</a>
				</div>

				<p class="text-muted mb-0">
					Already exploring internally?
					<a href="<?= e($login_url) ?>">Sign in to the admin shell</a>.
				</p>
			</div>
		</div>
	</div>
</section>
