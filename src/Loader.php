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
        $prefix = 'CoRex\Command\\';
        if (substr($class, 0, strlen($prefix)) != $prefix) {
            return;
        }
        $filename = str_replace($prefix, __DIR__ . '/', $class) . '.php';
        $filename = str_replace('\\', '/', $filename);

        // Hack to load tests prefixed with 'CoRex\Command\Tests';
        $testsPrefix = 'CoRex\Command\Tests\\';
        if (substr($class, 0, strlen($testsPrefix)) == $testsPrefix) {
            $filename = str_replace('src/Tests', 'tests', $filename);
        }

        if (!file_exists($filename)) {
            return;
        }
        require_once($filename);
    }
}