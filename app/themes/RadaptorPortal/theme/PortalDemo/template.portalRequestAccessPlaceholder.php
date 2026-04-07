<?php assert(isset($this) && $this instanceof Template); ?>
<?php $login_url = (string) ($this->props['loginUrl'] ?? '/login.html'); ?>
<section class="py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-xl-8">
				<div class="glass-card p-4 p-lg-5">
					<div class="row g-4 align-items-center">
						<div class="col-lg-6">
							<div class="hero-badge mb-3">
								<span class="badge-glow">
									<i class="bi bi-envelope-paper-heart me-2"></i>
									Early access placeholder
								</span>
							</div>
							<h2 class="mb-3">Request access without pretending the backend exists</h2>
							<p class="text-muted mb-3">
								This form is intentionally non-functional in the v1 portal demo. The later follow-up task will wire the email, magic-link, and newsletter flow behind this exact surface.
							</p>
							<p class="mb-0">
								If you already have credentials, you can use the
								<a href="<?= e($login_url) ?>">admin login</a> today.
							</p>
						</div>
						<div class="col-lg-6">
							<form class="glass-card p-4 border-0 h-100" action="#" method="post" novalidate>
								<div class="mb-3">
									<label class="form-label" for="request-access-email">Email address</label>
									<input id="request-access-email" class="form-control form-control-lg" type="email" placeholder="you@example.com" autocomplete="email">
								</div>
								<div class="form-check mb-4">
									<input id="request-access-newsletter" class="form-check-input" type="checkbox">
									<label class="form-check-label" for="request-access-newsletter">
										Keep me posted when the email-based access flow is ready.
									</label>
								</div>
								<button class="btn btn-primary btn-glow btn-lg w-100" type="button" disabled aria-disabled="true">
									Email delivery coming soon
								</button>
								<p class="text-muted small mb-0 mt-3">
									No data is submitted or stored in this slice. This is a UI placeholder only.
								</p>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
