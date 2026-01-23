<?php

declare(strict_types=1);

/**
 * GitHub OAuth functions
 */

class GitHubOAuth
{
    /**
     * Get GitHub authorization URL
     */
    public static function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id' => GITHUB_CLIENT_ID,
            'redirect_uri' => GITHUB_CALLBACK_URL,
            'scope' => 'user:email',
            'state' => self::generateState(),
        ]);
        return GITHUB_AUTH_URL . '?' . $params;
    }

    /**
     * Generate and store state for CSRF protection
     */
    private static function generateState(): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        return $state;
    }

    /**
     * Verify state parameter
     */
    public static function verifyState(string $state): bool
    {
        $valid = isset($_SESSION['oauth_state']) && hash_equals($_SESSION['oauth_state'], $state);
        unset($_SESSION['oauth_state']);
        return $valid;
    }

    /**
     * Exchange authorization code for access token
     */
    public static function getAccessToken(string $code): ?string
    {
        $response = self::httpPost(GITHUB_TOKEN_URL, [
            'client_id' => GITHUB_CLIENT_ID,
            'client_secret' => GITHUB_CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => GITHUB_CALLBACK_URL,
        ], ['Accept: application/json']);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Get user info from GitHub
     */
    public static function getUserInfo(string $accessToken): ?array
    {
        $userInfo = self::httpGet(GITHUB_USER_URL, $accessToken);
        $user = json_decode($userInfo, true);

        if (!$user || !isset($user['id'])) {
            return null;
        }

        // Get primary email
        $emailsResponse = self::httpGet(GITHUB_EMAILS_URL, $accessToken);
        $emails = json_decode($emailsResponse, true);

        $primaryEmail = null;
        if (is_array($emails)) {
            foreach ($emails as $email) {
                if ($email['primary'] ?? false) {
                    $primaryEmail = $email['email'];
                    break;
                }
            }
            // Fallback to first verified email
            if (!$primaryEmail) {
                foreach ($emails as $email) {
                    if ($email['verified'] ?? false) {
                        $primaryEmail = $email['email'];
                        break;
                    }
                }
            }
        }

        // Use public email as fallback
        if (!$primaryEmail) {
            $primaryEmail = $user['email'] ?? 'unknown@github.com';
        }

        return [
            'github_id' => (int) $user['id'],
            'email' => $primaryEmail,
            'name' => $user['name'] ?? $user['login'],
            'avatar_url' => $user['avatar_url'] ?? null,
            'login' => $user['login'],
        ];
    }

    /**
     * HTTP POST request
     */
    private static function httpPost(string $url, array $data, array $headers = []): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/x-www-form-urlencoded',
            ], $headers),
            CURLOPT_USERAGENT => 'Radaptor-Portal',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: '';
    }

    /**
     * HTTP GET request with bearer token
     */
    private static function httpGet(string $url, string $token): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ],
            CURLOPT_USERAGENT => 'Radaptor-Portal',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: '';
    }
}
