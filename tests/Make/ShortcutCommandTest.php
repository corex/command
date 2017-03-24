<?php

use CoRex\Command\Handler;
use CoRex\Command\Loader;
use CoRex\Command\SignatureHandler;
use CoRex\Support\System\Directory;
use PHPUnit\Framework\TestCase;

class ShortcutCommandTest extends TestCase
{
    private $tempDirectory;

    /**
     * Setup.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->tempDirectory = sys_get_temp_dir();
        $this->tempDirectory .= '/' . str_replace('.', '', microtime(true));
        Directory::make($this->tempDirectory);
    }

    /**
     * Tear down.
     */
    protected function tearDown()
    {
        parent::tearDown();
        Directory::delete($this->tempDirectory);
    }

    /**
     * Test run.
     */
    public function testRun()
    {
        // Initialize.
        require_once(dirname(dirname(__DIR__)) . '/src/Loader.php');
        Loader::initialize();
        (new Handler([], true, false));
        chdir($this->tempDirectory);

        // Test.
        SignatureHandler::call('make', 'shortcut', ['test', '-', '-'], true);
        $filename = $this->tempDirectory . '/test';
        $this->assertFileExists($filename);

        $filePermissions = substr(sprintf('%o', fileperms($filename)), -4);
        $this->assertEquals('0700', $filePermissions);

        $content = file_get_contents($filename);

        $this->assertContains('#!/usr/bin/env php', $content);
        $this->assertContains('// Uncomment and set path to your commands.', $content);
        $this->assertContains('// Can be called more than once.', $content);
    }
}
