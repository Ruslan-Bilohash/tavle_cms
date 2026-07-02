<?php
/**
 * Bilen CMS - SQLite Database (PDO)
 * No MySQL server required — single file at data/bilen.sqlite
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;
    private static bool $initialized = false;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            self::$instance = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::$instance->exec('PRAGMA foreign_keys = ON');
            self::$instance->exec('PRAGMA journal_mode = WAL');
            self::$instance->exec('PRAGMA synchronous = NORMAL');
        }

        if (!self::$initialized) {
            require_once __DIR__ . '/database/installer.php';
            DatabaseInstaller::ensureInstalled(self::$instance);
            self::$initialized = true;
        }

        return self::$instance;
    }

    public static function query(string $sql, string $types = '', array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        return self::query($sql, $types, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $row = self::query($sql, $types, $params)->fetch();
        return $row ?: null;
    }

    public static function insert(string $sql, string $types, array $params): int
    {
        self::query($sql, $types, $params);
        return (int) self::getInstance()->lastInsertId();
    }

    public static function execute(string $sql, string $types = '', array $params = []): int
    {
        return self::query($sql, $types, $params)->rowCount();
    }
}

function db(): PDO
{
    return Database::getInstance();
}