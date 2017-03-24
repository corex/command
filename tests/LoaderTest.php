<?php

use CoRex\Command\Loader;
use CoRex\Command\Tests\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * Test resolve class filename.
     */
    public function testResolveClassFilename()
    {
        $this->assertEquals(
            __DIR__ . '/Command/TestCommand.php',
            Loader::resolveClassFilename(TestCommand::class)
        );
    }

    /**
     * Test resolved class filename null.
     */
    public function testResolveClassFilenameNull()
    {
        $class = 'UnknownClassNotFound';
        $this->assertNull(Loader::resolveClassFilename($class));
    }
}
