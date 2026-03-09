<?php

declare(strict_types=1);

/**
 * Radaptor Portal - Main Entry Point
 * Simple router for the registration portal
 */

// Start session
session_start();

// Load configuration and dependencies
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/oauth.php';
require_once __DIR__ . '/../src/license.php';

// Get request path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// Simple router
try {
    match ($path) {
        '/' => handleLanding(),
        '/comparison' => handleComparison(),
        '/auth/github' => handleGitHubAuth(),
        '/auth/callback' => handleGitHubCallback(),
        '/dashboard' => handleDashboard(),
        '/logout' => handleLogout(),
        default => handle404(),
    };
} catch (Throwable $e) {
    handleError($e->getMessage());
}

/**
 * Landing page
 */
function handleLanding(): void
{
    // If already logged in, redirect to dashboard
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
        exit;
    }

    include __DIR__ . '/../src/views/landing.php';
}

/**
 * Public technical comparison page
 */
function handleComparison(): void
{
    include __DIR__ . '/../src/views/comparison.php';
}

/**
 * Initiate GitHub OAuth flow
 */
function handleGitHubAuth(): void
{
    // Check if GitHub credentials are configured
    if (empty(GITHUB_CLIENT_ID) || empty(GITHUB_CLIENT_SECRET)) {
        handleError('GitHub OAuth is not configured. Please set GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET in .env file.');
        return;
    }

    $authUrl = GitHubOAuth::getAuthUrl();
    header('Location: ' . $authUrl);
    exit;
}

/**
 * Handle GitHub OAuth callback
 */
function handleGitHubCallback(): void
{
    // Verify state
    $state = $_GET['state'] ?? '';
    if (!GitHubOAuth::verifyState($state)) {
        handleError('Invalid OAuth state. Please try again.');
        return;
    }

    // Check for error
    if (isset($_GET['error'])) {
        handleError('GitHub authorization failed: ' . ($_GET['error_description'] ?? $_GET['error']));
        return;
    }

    // Get authorization code
    $code = $_GET['code'] ?? '';
    if (empty($code)) {
        handleError('No authorization code received.');
        return;
    }

    // Exchange code for access token
    $accessToken = GitHubOAuth::getAccessToken($code);
    if (!$accessToken) {
        handleError('Failed to get access token from GitHub.');
        return;
    }

    // Get user info
    $githubUser = GitHubOAuth::getUserInfo($accessToken);
    if (!$githubUser) {
        handleError('Failed to get user info from GitHub.');
        return;
    }

    // Check if user exists
    $user = Database::findUserByGithubId($githubUser['github_id']);

    if ($user) {
        // Update last login
        Database::updateLastLogin($user['id']);

        // Update user info if changed
        Database::updateUser($user['id'], [
            'email' => $githubUser['email'],
            'name' => $githubUser['name'],
            'avatar_url' => $githubUser['avatar_url'],
        ]);
    } else {
        // Create new user with license key
        $licenseKey = License::generate($githubUser['email'], 'evaluation');

        $userId = Database::createUser([
            'github_id' => $githubUser['github_id'],
            'email' => $githubUser['email'],
            'name' => $githubUser['name'],
            'avatar_url' => $githubUser['avatar_url'],
            'license_key' => $licenseKey,
        ]);

        $user = Database::findUserById($userId);
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];

    // Redirect to dashboard
    header('Location: /dashboard');
    exit;
}

/**
 * Dashboard page
 */
function handleDashboard(): void
{
    // Check if logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: /');
        exit;
    }

    // Get user
    $user = Database::findUserById($_SESSION['user_id']);
    if (!$user) {
        unset($_SESSION['user_id']);
        header('Location: /');
        exit;
    }

    include __DIR__ . '/../src/views/dashboard.php';
}

/**
 * Logout
 */
function handleLogout(): void
{
    session_destroy();
    header('Location: /');
    exit;
}

/**
 * 404 Not Found
 */
function handle404(): void
{
    http_response_code(404);
    $error = 'Page not found.';
    include __DIR__ . '/../src/views/error.php';
}

/**
 * Error handler
 */
function handleError(string $message): void
{
    $error = $message;
    include __DIR__ . '/../src/views/error.php';
}
