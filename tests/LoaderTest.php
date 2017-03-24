<?php

use CoRex\Command\Loader;
use CoRex\Command\Tests\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * Test not loaded.
     */
    public function testNotLoaded()
    {
        $this->assertFalse(class_exists(TestCommand::class));
    }

    /**
     * Test loaded.
     */
    public function testLoaded()
    {
        require_once(dirname(__DIR__) . '/src/Loader.php');
        Loader::initialize();
        $this->assertTrue(class_exists(TestCommand::class));
    }
}
