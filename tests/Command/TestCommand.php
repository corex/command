<?php

namespace CoRex\Command\Tests\Command;

use CoRex\Command\BaseCommand;

class TestCommand extends BaseCommand
{
    protected $component = 'test';
    protected $signature = 'command
        {param1 : Parameter 1}
        {param2 : Parameter 2}';
    protected $description = 'Test command';
    protected $visible = true;

    /**
     * Run command.
     *
     * @return boolean
     */
    public function run()
    {
        print(json_encode([
            'param1' => $this->argument('param1'),
            'param2' => $this->argument('param2')
        ]));
    }
}