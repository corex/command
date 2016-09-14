<?php

namespace CoRex\Command\Make;

use CoRex\Command\BaseCommand;
use CoRex\Command\Console;
use CoRex\Command\Path;

class ShortcutCommand extends BaseCommand
{
    protected $component = 'make';
    protected $signature = 'shortcut
    	{name : Name of shortcut}
		{--delete : Delete existing shortcut}
		{--command= : Signature of command to execute}';
    protected $description = 'Make shortcut in current directory';
    protected $visible = true;

    /**
     * Run command.
     *
     * @throws \Exception
     */
    public function run()
    {
        $this->header($this->description);

        $cmdFilename = $this->argument('name');
        $currentDirectory = getcwd();

        // Set command.
        $shortcutComponent = '';
        $shortcutCommand = '';
        $command = $this->option('command');
        if ($command !== null) {
            $command = explode(':', $command);
            if (count($command) < 2) {
                return $this->error('Specified command is not valid.');
            }
            $shortcutComponent = $command[0];
            $shortcutCommand = $command[1];
        }

        // Check if existance or delete.
        if (file_exists($currentDirectory . '/' . $cmdFilename)) {
            if ($this->option('delete')) {
                unlink($currentDirectory . '/' . $cmdFilename);
            } else {
                Console::throwError($cmdFilename . ' already exists.');
            }
        }

        // Write stub.
        $stubFilename = Path::getFramework(['stubs', 'shortcut.stub']);
        $stub = file_get_contents($stubFilename);
        $stub = str_replace('{autoload}', Path::getAutoloadAsString(), $stub);
        $stub = str_replace('{component}', $shortcutComponent, $stub);
        $stub = str_replace('{command}', $shortcutCommand, $stub);
        file_put_contents($currentDirectory . '/' . $cmdFilename, $stub);
        chmod($currentDirectory . '/' . $cmdFilename, 0700);
        $this->info('Shortcut ' . $cmdFilename . ' created in ' . $currentDirectory);
    }
}