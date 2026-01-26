<?php
/**
 * PSR-4 style autoloader for ZUL Gallery
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery;

class Autoloader
{
    private static string $namespace = 'Zul\\Gallery\\';
    private static string $baseDir;

    public static function register(): void
    {
        self::$baseDir = ZUL_GALLERY_PLUGIN_DIR . 'includes/';
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        // Check if the class uses our namespace
        if (strpos($class, self::$namespace) !== 0) {
            return;
        }

        // Remove namespace prefix and convert to file path
        $relativeClass = substr($class, strlen(self::$namespace));
        $file = self::$baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
