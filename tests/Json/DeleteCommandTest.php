<?php

use CoRex\Command\Handler;
use CoRex\Command\Loader;
use CoRex\Command\SignatureHandler;
use CoRex\Support\System\Directory;
use PHPUnit\Framework\TestCase;

class DeleteCommandTest extends TestCase
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

        // Write test-file.
        $filename = $this->tempDirectory . '/test.json';
        $json = json_encode(['param1' => 'Param 1', 'param2' => 'Param 2', 'param3' => 'Param 3']);
        file_put_contents($filename, $json);

        // Test.
        SignatureHandler::call('json', 'delete', [$filename, 'param2'], true);
        $json = file_get_contents($filename);
        $this->assertEquals(
            ['param1' => 'Param 1', 'param3' => 'Param 3'],
            json_decode($json, true)
        );
    }
}
