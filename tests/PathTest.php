<?php

use CoRex\Command\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * Test autoload as string.
     */
    public function testAutoloadAsString()
    {
        $this->assertEquals(
            'dirname(dirname(__DIR__)) . \'/autoload.php\'',
            Path::autoloadAsString()
        );
    }
}
