<?php assert(isset($this) && $this instanceof Template); ?>
<?php if (class_exists('LibrariesRadaptorPortal')): ?>
	<?php $this->registerLibrary('__RADAPTOR_PORTAL_SITE'); ?>
<?php endif; ?>
<?php if ($this->isEditable() && class_exists('LibrariesRadaptorPortalAdmin')): ?>
	<?php $this->registerLibrary('__RADAPTOR_PORTAL_ADMIN_SITE'); ?>
<?php endif; ?>
<?php
$lang = (string)($this->props['lang'] ?? substr(Kernel::getLocale(), 0, 2));
$site_name = (string)($this->props['site_name'] ?? Config::APP_SITE_NAME->value());
$page_title = trim((string) $this->getTitle());
$document_title = $page_title !== '' ? $page_title . ' - ' . $site_name : $site_name;
$request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$current_path = (string) (parse_url($request_uri, PHP_URL_PATH) ?? '/');
$is_home = $current_path === '/' || $current_path === '/index.html';
$is_comparison = str_starts_with($current_path, '/comparison');
$is_request_access = str_starts_with($current_path, '/request-access');
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" data-bs-theme="dark">
<head>
	<meta charset="utf-8">
	<title><?= e($document_title) ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="<?= e((string) ($this->getDescription() ?: 'Radaptor Portal demo')); ?>">
	<link rel="shortcut icon" href="/assets/themes/radaptor-portal/images/radaptor-icon.svg">
	<link rel="icon" href="/assets/themes/radaptor-portal/images/radaptor-icon.svg" type="image/svg+xml">
	<?= $this->getRenderer()->getLibraryDebugInfo(); ?>
	<?= $this->getRenderer()->getCss(); ?>
</head>
<body class="portal-marketing-layout">
<?= $this->getRenderer()->fetchInnerHtml(); ?>

<div class="noise-overlay"></div>

<nav class="navbar navbar-expand-lg navbar-dark">
	<div class="container">
		<a class="navbar-brand" href="/">
			<img src="/assets/themes/radaptor-portal/images/radaptor-logo.svg" alt="<?= e($site_name) ?>" class="brand-logo" height="32">
		</a>

		<div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap justify-content-end">
			<a href="/" class="btn <?= $is_home ? 'btn-primary btn-glow' : 'btn-outline-light' ?> btn-sm">
				Home
			</a>
			<a href="/comparison/" class="btn <?= $is_comparison ? 'btn-primary btn-glow' : 'btn-outline-light' ?> btn-sm">
				Technical Comparison
			</a>
			<a href="/request-access/" class="btn <?= $is_request_access ? 'btn-primary btn-glow' : 'btn-outline-light' ?> btn-sm">
				Request Access
			</a>
			<a href="/login.html" class="btn btn-outline-light btn-sm">
				Admin Login
			</a>
		</div>
	</div>
</nav>

<main>
	<?= $this->fetchSlot('content'); ?>
</main>

<footer class="py-4 mt-auto">
	<div class="container text-center">
		<p class="text-muted mb-0">
			&copy; <?= date('Y') ?> <?= e($site_name) ?>.
			Preview build for product positioning, portal UX, and admin workflow demos.
		</p>
	</div>
</footer>

<?= $this->fetchSlot('page_chrome'); ?>
<?= $this->getRenderer()->getJs(); ?>
<?= $this->getRenderer()->fetchClosingHtml(); ?>
</body>
</html>
