<?php

declare(strict_types=1);

/**
 * Database connection and initialization
 */

class Database
{
    private static ?PDO $pdo = null;

    /**
     * Get database connection (singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO('sqlite:' . DATABASE_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::initSchema();
        }
        return self::$pdo;
    }

    /**
     * Initialize database schema if needed
     */
    private static function initSchema(): void
    {
        self::$pdo->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                github_id INTEGER UNIQUE NOT NULL,
                email TEXT NOT NULL,
                name TEXT,
                avatar_url TEXT,
                tier TEXT DEFAULT "evaluation",
                license_key TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
    }

    /**
     * Find user by GitHub ID
     */
    public static function findUserByGithubId(int $githubId): ?array
    {
        $stmt = self::getConnection()->prepare('SELECT * FROM users WHERE github_id = ?');
        $stmt->execute([$githubId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find user by ID
     */
    public static function findUserById(int $id): ?array
    {
        $stmt = self::getConnection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create new user
     */
    public static function createUser(array $data): int
    {
        $stmt = self::getConnection()->prepare('
            INSERT INTO users (github_id, email, name, avatar_url, tier, license_key)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $data['github_id'],
            $data['email'],
            $data['name'] ?? null,
            $data['avatar_url'] ?? null,
            $data['tier'] ?? 'evaluation',
            $data['license_key'],
        ]);
        return (int) self::getConnection()->lastInsertId();
    }

    /**
     * Update user last login
     */
    public static function updateLastLogin(int $userId): void
    {
        $stmt = self::getConnection()->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Update user info
     */
    public static function updateUser(int $userId, array $data): void
    {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $userId;

        $stmt = self::getConnection()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($params);
    }
}
