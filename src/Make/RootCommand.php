<?php

namespace CoRex\Command\Make;

use CoRex\Command\BaseCommand;
use CoRex\Command\Console;
use CoRex\Command\Path;

class RootCommand extends BaseCommand
{
    protected $component = 'make';
    protected $signature = 'root
		{--delete : Delete existing crcmd}
		{--name= : Name of command. Default crcmd.}';
    protected $description = 'Make root-command (crcmd?) in current directory';
    protected $visible = true;

    /**
     * Run command.
     *
     * @throws \Exception
     */
    public function run()
    {
        $this->header($this->description);

        // Make sure this command only works if installed through composer.
        if (!$this->throughComposer()) {
            Console::throwError('This command is not allowed unless installed through composer.');
        }

        $cmdFilename = $this->option('name');
        if ($cmdFilename === null) {
            $cmdFilename = 'crcmd';
        }
        $currentDirectory = getcwd();

        // Check if existance or delete.
        if (file_exists($currentDirectory . '/' . $cmdFilename)) {
            if ($this->option('delete')) {
                unlink($currentDirectory . '/' . $cmdFilename);
            } else {
                Console::throwError($cmdFilename . ' already exists.');
            }
        }

        // Write stub.
        $stubFilename = Path::getFramework(['stubs', 'crcmd.stub']);
        $stub = file_get_contents($stubFilename);
        $stub = str_replace('{autoload}', Path::getAutoloadAsString(), $stub);
        file_put_contents($currentDirectory . '/' . $cmdFilename, $stub);
        chmod($currentDirectory . '/' . $cmdFilename, 0700);
        $this->info('crcmd created in ' . $currentDirectory);
    }
}