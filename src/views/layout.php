<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Radaptor') ?></title>

    <!-- Bootstrap Icons (CDN - lightweight, icon font only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Compiled Bootstrap + Theme (from SCSS) -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Noise overlay -->
    <div class="noise-overlay"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="/assets/images/radaptor-logo.svg" alt="Radaptor" class="brand-logo" height="32">
            </a>

            <div class="d-flex align-items-center gap-3">
                <?php if (isset($user)): ?>
                    <?php if ($user['avatar_url']): ?>
                    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" class="avatar-sm rounded-circle">
                    <?php endif; ?>
                    <span class="text-light"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
                    <a href="/logout" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="/auth/github" class="btn btn-primary btn-glow">
                        <i class="bi bi-github me-2"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="py-4 mt-auto">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> Radaptor. Built with <i class="bi bi-heart-fill text-danger"></i> for developers.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
