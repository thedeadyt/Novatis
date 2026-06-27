<?php
/**
 * Helper Functions
 * Global utility functions for the application
 */

if (!function_exists('env')) {
    /**
     * Get environment variable value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null) {
        // Try $_ENV first (Dotenv v5 uses this)
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        // Then try $_SERVER
        elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
        // Finally try getenv()
        else {
            $value = getenv($key);
            if ($value === false) {
                return $default;
            }
        }

        // Convert string representations to actual types
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     *
     * @param string $key Format: 'file.key.subkey'
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null) {
        static $configs = [];

        $parts = explode('.', $key);
        $file = array_shift($parts);

        // Load config file if not loaded
        if (!isset($configs[$file])) {
            $configPath = __DIR__ . '/../config/' . $file . '.php';
            if (file_exists($configPath)) {
                $configs[$file] = require $configPath;
            } else {
                return $default;
            }
        }

        // Navigate through array
        $value = $configs[$file];
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path of application
     *
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string {
        return __DIR__ . '/../' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public path
     *
     * @param string $path
     * @return string
     */
    function public_path(string $path = ''): string {
        return base_path('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string {
        return base_path('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars): void {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable
     *
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars): void {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}
