<?php assert(isset($this) && $this instanceof Template); ?>
<?php
$comparison_url = (string) ($this->props['comparisonUrl'] ?? '/comparison/');
$roadmap_url = (string) ($this->props['roadmapUrl'] ?? '/roadmap/');
$request_access_url = (string) ($this->props['requestAccessUrl'] ?? '/request-access/');
$render_i18n_html = static function (string $key, array $html_params): string {
	$params = [];
	$replacements = [];

	foreach ($html_params as $name => $html) {
		$marker = '__RADAPTOR_PORTAL_I18N_HTML_' . strtoupper((string) $name) . '__';
		$params[(string) $name] = $marker;
		$replacements[$marker] = (string) $html;
	}

	return strtr(e(t($key, $params)), $replacements);
};
$widget_driven_html = '<span class="gradient-text">' . e(t('portal.hero.title.widget_driven')) . '</span>';
$radaptor_html = '<span class="gradient-text">' . e(t('portal.brand.radaptor')) . '</span>';
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
					<?= $render_i18n_html('portal.hero.title.line1', ['widgetDriven' => $widget_driven_html]) ?><br>
					<?= $render_i18n_html('portal.hero.title.line2', ['radaptor' => $radaptor_html]) ?>
				</h1>

				<p class="hero-description mb-5">
					<?= e(t('portal.hero.description')) ?>
				</p>

				<div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-4">
					<a href="<?= e($comparison_url) ?>" class="btn btn-primary btn-glow btn-lg">
						<i class="bi bi-diagram-3 me-2"></i>
						<?= e(t('portal.nav.comparison')) ?>
					</a>
					<a href="<?= e($roadmap_url) ?>" class="btn btn-outline-light btn-lg">
						<i class="bi bi-signpost-split me-2"></i>
						<?= e(t('portal.nav.roadmap')) ?>
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
