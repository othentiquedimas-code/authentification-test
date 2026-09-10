<?php

declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';

function env(string $key, ?string $default = null): ?string
{
    static $values;

    if ($values === null) {
        $envPath = BASE_PATH . '/.env';
        $values = is_file($envPath)
            ? parse_ini_file($envPath, false, INI_SCANNER_RAW)
            : [];
    }

    if (!is_array($values) || !array_key_exists($key, $values)) {
        return $default;
    }

    return trim((string) $values[$key]);
}