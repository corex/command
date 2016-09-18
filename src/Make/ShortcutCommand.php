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
        {command : Signature of command to execute (Specify - for list of commands)}
        {filename : Existing file to use as template (Specify - for default)}
        {--delete : Delete existing shortcut}';
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

        $newFilename = $this->argument('name');
        $command = $this->argument('command');
        $existingFilename = $this->argument('filename');
        $currentDirectory = getcwd();

        // Set command.
        $shortcutComponent = '';
        $shortcutCommand = '';
        if ($command !== null && $command != '-') {
            $command = explode(':', $command);
            if (count($command) < 2) {
                return $this->error('Specified command is not valid.');
            }
            $shortcutComponent = $command[0];
            $shortcutCommand = $command[1];
        }

        // Check if existance or delete.
        if (file_exists($currentDirectory . '/' . $newFilename)) {
            if ($this->option('delete')) {
                unlink($currentDirectory . '/' . $newFilename);
            } else {
                Console::throwError($newFilename . ' already exists.');
            }
        }

        // Get stub.
        if ($existingFilename == '-') {
            $stubFilename = Path::getFramework(['stubs', 'shortcut.stub']);
        } else {
            $stubFilename = $existingFilename;
        }
        $stub = file_get_contents($stubFilename);

        // Replace tokens.
        $stub = str_replace('{autoload}', Path::getAutoloadAsString(), $stub);
        $stub = explode("\n", $stub);
        if (count($stub) > 0) {
            foreach ($stub as $index => $line) {
                $executeHandlerPrefix = '$handler->execute(';
                if (substr(trim($line), 0, strlen($executeHandlerPrefix)) == $executeHandlerPrefix) {
                    $line = '    ' . $executeHandlerPrefix . '\'' . $shortcutComponent . '\', \'' . $shortcutCommand . '\');';
                }
                $stub[$index] = $line;
            }
        }
        $stub = implode("\n", $stub);

        // Write stub.
        file_put_contents($currentDirectory . '/' . $newFilename, $stub);
        chmod($currentDirectory . '/' . $newFilename, 0700);
        $this->info('Shortcut ' . $newFilename . ' created in ' . $currentDirectory);
    }
}