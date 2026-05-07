<?php assert(isset($this) && $this instanceof Template); ?>
<?php
$comparison_url = (string) ($this->props['comparisonUrl'] ?? '/comparison/');
$request_access_url = (string) ($this->props['requestAccessUrl'] ?? '/request-access/');
?>
<section class="hero min-vh-100 d-flex align-items-center">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-9 text-center">
				<div class="hero-badge mb-4">
					<span class="badge-glow">
							<i class="bi bi-stars me-2"></i>
							<?= e(t('portal.hero.badge')) ?>
					</span>
				</div>

				<h1 class="hero-title mb-4">
					<?= e(t('portal.hero.title.build')) ?> <span class="gradient-text"><?= e(t('portal.hero.title.widget_driven')) ?></span> <?= e(t('portal.hero.title.applications')) ?><br>
					<?= e(t('portal.hero.title.with')) ?> <span class="gradient-text"><?= e(t('portal.brand.radaptor')) ?></span>
				</h1>

				<p class="hero-description mb-5">
					<?= e(t('portal.hero.description')) ?>
				</p>

				<div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-4">
					<a href="<?= e($comparison_url) ?>" class="btn btn-primary btn-glow btn-lg">
						<i class="bi bi-diagram-3 me-2"></i>
						<?= e(t('portal.nav.comparison')) ?>
					</a>
					<a href="<?= e($request_access_url) ?>" class="btn btn-outline-light btn-lg">
						<i class="bi bi-arrow-right-circle me-2"></i>
						<?= e(t('portal.nav.request_access')) ?>
					</a>
				</div>

				<p class="text-muted mb-0">
					<?= e(t('portal.hero.help')) ?>
				</p>
			</div>
		</div>
	</div>
</section>
