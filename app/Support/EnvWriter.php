<?php

namespace App\Support;

use RuntimeException;

final class EnvWriter
{
    /**
     * @param  array<string, string|int|bool|null>  $pairs
     */
    public static function merge(array $pairs): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            throw new RuntimeException('.env dosyası bulunamadı. Önce .env.example dosyasını .env olarak kopyalayın.');
        }
        if (! is_writable($path)) {
            throw new RuntimeException('.env dosyası yazılabilir değil (chmod / sahip kontrol edin).');
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('.env okunamadı.');
        }

        foreach ($pairs as $key => $value) {
            if (! is_string($key) || $key === '' || str_contains($key, "\n")) {
                continue;
            }
            $line = $key.'='.self::formatValue($value);
            if (preg_match('/^'.preg_quote($key, '/').'=/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content, 1);
            } else {
                $content = rtrim($content)."\n".$line."\n";
            }
        }

        file_put_contents($path, $content);
    }

    private static function formatValue(string|int|bool|null $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value)) {
            return (string) $value;
        }

        if ($value === '') {
            return '""';
        }

        if (preg_match('/[\s#"\'\\\\]/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
