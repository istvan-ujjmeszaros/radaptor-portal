<?php assert(isset($this) && $this instanceof Template); ?>
<?php $items = is_array($this->props['items'] ?? null) ? $this->props['items'] : []; ?>
<section class="py-5">
	<div class="container">
		<div class="row justify-content-center mb-5">
			<div class="col-lg-8 text-center">
				<h2 class="mb-3">Designed for explicit product and platform work</h2>
				<p class="text-muted mb-0">
					The same application surface can power public pages, operational tools, admin workflows, and future non-HTML channels without splitting into unrelated stacks.
				</p>
			</div>
		</div>

		<div class="row g-4">
			<?php foreach ($items as $item): ?>
				<div class="col-md-6 col-xl-4">
					<div class="feature-card glass-card p-4 h-100">
						<div class="feature-icon mb-3">
							<i class="bi <?= e((string) ($item['icon'] ?? 'bi-stars')); ?>"></i>
						</div>
						<h3 class="h5 mb-2"><?= e((string) ($item['title'] ?? '')); ?></h3>
						<p class="text-muted mb-0"><?= e((string) ($item['description'] ?? '')); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
