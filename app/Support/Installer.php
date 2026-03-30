<?php

namespace App\Support;

final class Installer
{
    public static function lockPath(): string
    {
        return storage_path('framework/install.lock');
    }

    public static function isComplete(): bool
    {
        return is_file(self::lockPath());
    }

    public static function markComplete(): void
    {
        $dir = dirname(self::lockPath());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::lockPath(),
            json_encode(['completed_at' => now()->toIso8601String()], JSON_THROW_ON_ERROR),
        );
    }
}
