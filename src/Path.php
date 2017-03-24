<?php

namespace CoRex\Command;

use CoRex\Support\System\Path as SupportPath;

class Path extends SupportPath
{
    /**
     * Get package path.
     *
     * @return string
     */
    protected static function packagePath()
    {
        return dirname(__DIR__);
    }

    /**
     * Get full path + filename "vendor/autoload.php" as string.
     *
     * @return string
     */
    public static function autoloadAsString()
    {
        $pathCurrent = trim(str_replace('\\', '/', getcwd()), '/');
        $pathAutoload = trim(self::root(['vendor', 'autoload.php']), '/');
        $pathCurrent = explode('/', $pathCurrent);
        $pathAutoload = explode('/', $pathAutoload);

        // Remove shared path.
        $pathShared = [];
        $index = 0;
        while ($index < count($pathCurrent) && $index < count($pathAutoload)) {
            if ($pathCurrent[$index] == $pathAutoload[$index]) {
                $pathShared[] = $pathCurrent[$index];
                $pathCurrent[$index] = null;
                $pathAutoload[$index] = null;
            }
            $index++;
        }
        $pathCurrent = array_filter($pathCurrent);
        $pathAutoload = array_filter($pathAutoload);

        // Build path.
        $path = '__DIR__';
        if (count($pathCurrent) > 0) {
            for ($c1 = 0; $c1 < count($pathCurrent); $c1++) {
                $path = 'dirname(' . $path . ')';
            }
        }
        $path .= ' . \'/' . implode('/', $pathAutoload) . '\'';

        return $path;
    }
}