<?php

namespace CoRex\Command\Make;

use CoRex\Command\BaseCommand;
use CoRex\Command\Path;

class Command extends BaseCommand
{
    protected $component = 'make';
    protected $signature = 'command
        {class : Name of class}
        {namespace : Namespace of command (/ will be converted to \)}';
    protected $description = 'Make command in current directory';
    protected $visible = true;

    /**
     * Run command.
     */
    public function run()
    {
        $class = ucfirst($this->argument('class'));
        $namespace = $this->argument('namespace');
        $namespace = str_replace('/', '\\', $namespace);

        // Make sure class ends with "Command".
        if (substr($class, -7) != 'Command') {
            $class .= 'Command';
        }

        // Write stub.
        $commandFilename = $class . '.php';
        $stubFilename = Path::packageCurrent(['stubs', 'command.stub']);
        $stub = file_get_contents($stubFilename);
        $stub = str_replace('{namespace}', $namespace, $stub);
        $stub = str_replace('{Class}', $class, $stub);
        file_put_contents($commandFilename, $stub);

        $this->info($commandFilename . ' created.');
    }
}