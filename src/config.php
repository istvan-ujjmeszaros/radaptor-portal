<?php

declare(strict_types=1);

/**
 * Configuration loader
 * Loads environment variables from .env file
 */

// Load .env file if it exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        // Parse KEY=value
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Configuration constants
define('GITHUB_CLIENT_ID', $_ENV['GITHUB_CLIENT_ID'] ?? '');
define('GITHUB_CLIENT_SECRET', $_ENV['GITHUB_CLIENT_SECRET'] ?? '');
define('GITHUB_CALLBACK_URL', $_ENV['GITHUB_CALLBACK_URL'] ?? 'http://localhost:8020/auth/callback');
define('LICENSE_SECRET', $_ENV['LICENSE_SECRET'] ?? 'default_secret_change_me');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8020');

// GitHub OAuth URLs
define('GITHUB_AUTH_URL', 'https://github.com/login/oauth/authorize');
define('GITHUB_TOKEN_URL', 'https://github.com/login/oauth/access_token');
define('GITHUB_USER_URL', 'https://api.github.com/user');
define('GITHUB_EMAILS_URL', 'https://api.github.com/user/emails');

// Database path
define('DATABASE_PATH', dirname(__DIR__) . '/data/portal.db');
