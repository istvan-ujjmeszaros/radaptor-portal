<?php assert(isset($this) && $this instanceof Template); ?>
<?php if (class_exists('LibrariesRadaptorPortal')): ?>
	<?php $this->registerLibrary(LibrariesRadaptorPortal::REQUEST_ACCESS_FORM); ?>
<?php endif; ?>
<?php $submit_url = (string) ($this->props['submitUrl'] ?? event_url('portalAccessRequest.submit')); ?>
<?php $request_state = (string) ($this->props['requestState'] ?? ''); ?>
<?php
$state_meta = [
	PortalAccessRequestService::UI_STATE_SUBMITTED => [
		'class' => 'alert alert-success',
		'title' => t('portal.access.state.submitted.title'),
		'body' => t('portal.access.state.submitted.body'),
	],
	PortalAccessRequestService::UI_STATE_CONFIRMED => [
		'class' => 'alert alert-success',
		'title' => t('portal.access.state.confirmed.title'),
		'body' => t('portal.access.state.confirmed.body'),
	],
	PortalAccessRequestService::UI_STATE_EXPIRED => [
		'class' => 'alert alert-warning',
		'title' => t('portal.access.state.expired.title'),
		'body' => t('portal.access.state.expired.body'),
	],
	PortalAccessRequestService::UI_STATE_INVALID => [
		'class' => 'alert alert-danger',
		'title' => t('portal.access.state.invalid.title'),
		'body' => t('portal.access.state.invalid.body'),
	],
	PortalAccessRequestService::UI_STATE_INVALID_EMAIL => [
		'class' => 'alert alert-danger',
		'title' => t('portal.access.state.invalid_email.title'),
		'body' => t('portal.access.state.invalid_email.body'),
	],
	PortalAccessRequestService::UI_STATE_ERROR => [
		'class' => 'alert alert-danger',
		'title' => t('portal.access.state.error.title'),
		'body' => t('portal.access.state.error.body'),
	],
];
$banner = $state_meta[$request_state] ?? null;
$hide_form = in_array($request_state, [
	PortalAccessRequestService::UI_STATE_SUBMITTED,
	PortalAccessRequestService::UI_STATE_CONFIRMED,
], true);
?>
<section class="py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-xl-8">
				<div class="glass-card p-4 p-lg-5">
					<?php if (is_array($banner)): ?>
						<div class="<?= e($banner['class']) ?> mb-4" role="status">
							<h2 class="h5 mb-2"><?= e($banner['title']) ?></h2>
							<p class="mb-0"><?= e($banner['body']) ?></p>
						</div>
					<?php endif; ?>
					<?php if ($hide_form): ?>
						<div class="row justify-content-center">
							<div class="col-lg-8">
								<div class="text-center">
									<div class="hero-badge mb-3">
											<span class="badge-glow">
												<i class="bi bi-envelope-paper-heart me-2"></i>
												<?= e(t('portal.access.badge')) ?>
											</span>
										</div>
										<h2 class="mb-3"><?= e($request_state === PortalAccessRequestService::UI_STATE_CONFIRMED ? t('portal.access.confirmed_heading') : t('portal.access.waiting_heading')) ?></h2>
										<p class="text-muted mb-3">
											<?php if ($request_state === PortalAccessRequestService::UI_STATE_CONFIRMED): ?>
												<?= e(t('portal.access.confirmed_note')) ?>
											<?php else: ?>
												<?= e(t('portal.access.waiting_note')) ?>
											<?php endif; ?>
										</p>
										<p class="mb-0 text-muted">
											<?= e(t('portal.access.scope_note')) ?>
										</p>
								</div>
							</div>
						</div>
					<?php else: ?>
						<div class="row g-4 align-items-center">
							<div class="col-lg-6">
								<div class="hero-badge mb-3">
										<span class="badge-glow">
											<i class="bi bi-envelope-paper-heart me-2"></i>
											<?= e(t('portal.access.badge')) ?>
										</span>
									</div>
									<h2 class="mb-3"><?= e(t('portal.access.form_title')) ?></h2>
									<p class="text-muted mb-3">
										<?= e(t('portal.access.form_intro')) ?>
									</p>
									<p class="mb-0 text-muted">
										<?= e(t('portal.access.form_scope')) ?>
									</p>
							</div>
							<div class="col-lg-6">
								<form class="glass-card p-4 border-0 h-100" action="<?= e($submit_url) ?>" method="post" novalidate data-request-access-form>
									<div class="mb-3">
										<label class="form-label" for="request-access-email"><?= e(t('portal.access.email_label')) ?></label>
										<input id="request-access-email" name="email" class="form-control form-control-lg" type="email" placeholder="you@example.com" autocomplete="email" required>
									</div>
									<input type="hidden" name="locale" value="">
									<input type="hidden" name="timezone" value="">
									<div class="form-check mb-4">
										<input id="request-access-newsletter" name="wants_updates" value="1" class="form-check-input" type="checkbox">
										<label class="form-check-label" for="request-access-newsletter">
											<?= e(t('portal.access.updates_label')) ?>
										</label>
									</div>
									<button class="btn btn-primary btn-glow btn-lg w-100" type="submit">
										<?= e(t('portal.access.submit')) ?>
									</button>
									<p class="text-muted small mb-0 mt-3">
										<?= e(t('portal.access.confirm_note')) ?>
									</p>
								</form>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
