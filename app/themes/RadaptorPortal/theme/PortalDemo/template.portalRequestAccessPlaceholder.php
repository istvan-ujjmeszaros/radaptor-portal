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
		'title' => 'Check your inbox',
		'body' => 'If the address can receive early-access requests, we just sent a confirmation link. Once you confirm it, the request is complete.',
	],
	PortalAccessRequestService::UI_STATE_CONFIRMED => [
		'class' => 'alert alert-success',
		'title' => 'Your request is confirmed',
		'body' => 'Thanks. Your email address is confirmed and your early-access request is now on file. We will reach out at this address when the next access step is ready.',
	],
	PortalAccessRequestService::UI_STATE_EXPIRED => [
		'class' => 'alert alert-warning',
		'title' => 'That confirmation link expired',
		'body' => 'Submit the form again and we will send you a fresh confirmation email.',
	],
	PortalAccessRequestService::UI_STATE_INVALID => [
		'class' => 'alert alert-danger',
		'title' => 'That confirmation link is not valid',
		'body' => 'Please submit the form again if you still want access.',
	],
	PortalAccessRequestService::UI_STATE_INVALID_EMAIL => [
		'class' => 'alert alert-danger',
		'title' => 'Enter a valid email address',
		'body' => 'We could not create the request because the email address was invalid.',
	],
	PortalAccessRequestService::UI_STATE_ERROR => [
		'class' => 'alert alert-danger',
		'title' => 'Something went wrong',
		'body' => 'Please try again in a moment.',
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
											Early access
										</span>
									</div>
									<h2 class="mb-3"><?= $request_state === PortalAccessRequestService::UI_STATE_CONFIRMED ? 'Confirmed for follow-up' : 'Waiting for confirmation' ?></h2>
									<p class="text-muted mb-3">
										<?php if ($request_state === PortalAccessRequestService::UI_STATE_CONFIRMED): ?>
											There is nothing else you need to do right now. We will contact this address when we are ready to move you into the next platform-access step.
										<?php else: ?>
											The request is waiting on the confirmation email. After you click that link, your address is marked as confirmed and the request is complete.
										<?php endif; ?>
									</p>
									<p class="mb-0 text-muted">
										This page is only for early-access requests and follow-up confirmation.
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
										Early access
									</span>
								</div>
								<h2 class="mb-3">Request early access</h2>
								<p class="text-muted mb-3">
									Send us the email address you want to use for early access to the Radaptor Platform. We will ask you to confirm it before we treat the request as real.
								</p>
								<p class="mb-0 text-muted">
									We use this page only for access requests and platform follow-up, not as a general product login entry point.
								</p>
							</div>
							<div class="col-lg-6">
								<form class="glass-card p-4 border-0 h-100" action="<?= e($submit_url) ?>" method="post" novalidate data-request-access-form>
									<div class="mb-3">
										<label class="form-label" for="request-access-email">Email address</label>
										<input id="request-access-email" name="email" class="form-control form-control-lg" type="email" placeholder="you@example.com" autocomplete="email" required>
									</div>
									<input type="hidden" name="locale" value="">
									<input type="hidden" name="timezone" value="">
									<div class="form-check mb-4">
										<input id="request-access-newsletter" name="wants_updates" value="1" class="form-check-input" type="checkbox">
										<label class="form-check-label" for="request-access-newsletter">
											Keep me posted about Radaptor Platform updates as well.
										</label>
									</div>
									<button class="btn btn-primary btn-glow btn-lg w-100" type="submit">
										Request access
									</button>
									<p class="text-muted small mb-0 mt-3">
										We will only accept the request after you confirm the email address from the message we send you.
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
