<?php

namespace CoRex\Command;

class Loader
{
    /**
     * Initialize auto-loader.
     */
    public static function initialize()
    {
        spl_autoload_register(__NAMESPACE__ . '\Loader::autoLoader');
    }

    /**
     * Auto-loader.
     *
     * @param string $class
     */
    public static function autoLoader($class)
    {
        $filename = self::resolveClassFilename($class);
        if ($filename !== null && file_exists($filename)) {
            require_once($filename);
        }
    }

    /**
     * Resolve class filename.
     *
     * @param string $class
     * @return string
     */
    public static function resolveClassFilename($class)
    {
        $prefix = 'CoRex\Command\\';
        if (substr($class, 0, strlen($prefix)) != $prefix) {
            return null;
        }
        $filename = str_replace($prefix, __DIR__ . '/', $class) . '.php';
        $filename = str_replace('\\', '/', $filename);

        // Hack to load tests prefixed with 'CoRex\Command\Tests';
        $testsPrefix = 'CoRex\Command\Tests\\';
        if (substr($class, 0, strlen($testsPrefix)) == $testsPrefix) {
            $filename = str_replace('src/Tests', 'tests', $filename);
        }

        return $filename;
    }
}