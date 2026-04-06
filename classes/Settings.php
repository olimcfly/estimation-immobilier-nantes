<?php

class Settings
{
    private static array $cache = [];

    public static function get(string $key, $default = null): ?string
    {
        if (empty(self::$cache)) {
            self::loadAll();
        }

        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        $db = Database::getConnection();
        $db->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        )->execute([$key, $value]);

        self::$cache[$key] = $value;
    }

    public static function getGroup(string $group): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT setting_key, setting_value FROM settings
             WHERE setting_group = ?'
        );
        $stmt->execute([$group]);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[$row['setting_key']] = $row['setting_value'];
        }

        return $results;
    }

    public static function setMany(array $data): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        foreach ($data as $key => $value) {
            $stmt->execute([$key, $value]);
            self::$cache[$key] = $value;
        }
    }

    private static function loadAll(): void
    {
        $db = Database::getConnection();
        $rows = $db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();

        foreach ($rows as $row) {
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
    }
}
