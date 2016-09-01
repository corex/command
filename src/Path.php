<?php

namespace CoRex\Command;

class Path
{
    /**
     * Get path to root of site (works outside Joomla).
     *
     * @param array $segments Default [].
     * @return string
     */
    public static function getRoot(array $segments = [])
    {
        $path = dirname(dirname(dirname(dirname(__DIR__))));
        $path = str_replace('\\', '/', $path);
        if (count($segments) > 0) {
            $path .= '/' . implode('/', $segments);
        }
        return $path;
    }

    /**
     * Get path to framework.
     *
     * @param array $segments Default [].
     * @return string
     */
    public static function getFramework(array $segments = [])
    {
        $path = dirname(__DIR__);
        $path = str_replace('\\', '/', $path);
        if (count($segments) > 0) {
            $path .= '/' . implode('/', $segments);
        }
        return $path;
    }

    /**
     * Get full path + filename "vendor/autoload.php".
     *
     * @return string
     */
    public static function getAutoload()
    {
        return self::getRoot(['vendor', 'autoload.php']);
    }

    /**
     * Get full path + filename "vendor/autoload.php" as string.
     *
     * @return string
     */
    public static function getAutoloadAsString()
    {
        $pathCurrent = trim(str_replace('\\', '/', getcwd()), '/');
        $pathAutoload = trim(self::getRoot(['vendor', 'autoload.php']), '/');
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
