<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal .env loader for CodeIgniter 3.
 *
 * Reads a `.env` file from the repo root (one level above index.php)
 * and merges its key=value pairs into $_SERVER / $_ENV / getenv().
 *
 * Loaded from index.php BEFORE CodeIgniter boots so that CI_ENV is
 * available for the ENVIRONMENT define. Config files (database.php,
 * authorize_net.php, email.php) read values via env('KEY', $default).
 *
 * Format:
 *   KEY=value           (no quotes needed)
 *   KEY="quoted value"  (quotes are stripped)
 *   # comment lines and blank lines are ignored
 */

if (!function_exists('pearland411_load_env')) {
    function pearland411_load_env($path)
    {
        if (!is_readable($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
        return true;
    }
}

if (!function_exists('env')) {
    /**
     * Read a value from the environment with a default fallback.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        // Cast common boolean strings
        $lower = strtolower($value);
        if ($lower === 'true')  return true;
        if ($lower === 'false') return false;
        if ($lower === 'null')  return null;
        return $value;
    }
}
