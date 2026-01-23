<?php

declare(strict_types=1);

/**
 * License key generation and validation
 */

class License
{
    /**
     * Generate a license key for a user
     */
    public static function generate(string $email, string $tier = 'evaluation', ?int $expires = null): string
    {
        $payload = [
            'email' => $email,
            'tier' => $tier,
            'issued' => time(),
            'expires' => $expires,
        ];

        $encodedPayload = self::base64UrlEncode(json_encode($payload));
        $signature = self::sign($encodedPayload);

        return $encodedPayload . '.' . $signature;
    }

    /**
     * Validate a license key
     */
    public static function validate(string $licenseKey): ?array
    {
        $parts = explode('.', $licenseKey);
        if (count($parts) !== 2) {
            return null;
        }

        [$encodedPayload, $signature] = $parts;

        // Verify signature
        $expectedSignature = self::sign($encodedPayload);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);
        if (!$payload) {
            return null;
        }

        // Check expiration
        if (isset($payload['expires']) && $payload['expires'] !== null && $payload['expires'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Get human-readable tier name
     */
    public static function getTierName(string $tier): string
    {
        return match ($tier) {
            'evaluation' => 'Evaluation',
            'solo' => 'Solo Developer',
            'agency' => 'Agency',
            'enterprise' => 'Enterprise',
            default => ucfirst($tier),
        };
    }

    /**
     * Get tier features
     */
    public static function getTierFeatures(string $tier): array
    {
        return match ($tier) {
            'evaluation' => [
                'Full framework access',
                'Development use only',
                'Community support',
            ],
            'solo' => [
                'Full framework access',
                'Single developer license',
                'Production use',
                'Email support',
            ],
            'agency' => [
                'Full framework access',
                'Up to 10 developers',
                'Production use',
                'Priority support',
                'White-label option',
            ],
            'enterprise' => [
                'Full framework access',
                'Unlimited developers',
                'Production use',
                'Dedicated support',
                'Custom features',
                'SLA guarantee',
            ],
            default => ['Full framework access'],
        };
    }

    /**
     * Create HMAC signature
     */
    private static function sign(string $data): string
    {
        $signature = hash_hmac('sha256', $data, LICENSE_SECRET, true);
        return self::base64UrlEncode($signature);
    }

    /**
     * Base64 URL-safe encode
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe decode
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
