<?php

use CoRex\Command\Commands;
use CoRex\Command\Handler;
use CoRex\Command\Loader;
use CoRex\Support\System\Directory;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
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
        Commands::getInstance()->call('make', 'command', ['Test', 'Test/Namespace'], true);
        $filename = $this->tempDirectory . '/TestCommand.php';
        $this->assertFileExists($filename);
        $content = file_get_contents($filename);
        $this->assertContains('class TestCommand extends BaseCommand', $content);
    }
}