<?php

namespace CoRex\Command\Json;

use CoRex\Command\BaseCommand;
use CoRex\Support\Container;

class GetCommand extends BaseCommand
{
    protected $component = 'json';
    protected $signature = 'get
        {filename : Filename of json}
        {key : Key (dot notation supported)}
        {defaultValue : Default value of key does not exist}';
    protected $description = 'Get key';
    protected $visible = true;

    /**
     * Run command.
     *
     * @throws \Exception
     */
    public function run()
    {
        // Get arguments.
        $filename = $this->argument('filename');
        $key = $this->argument('key');
        $defaultValue = $this->argument('defaultValue');

        if (!file_exists($filename)) {
            throw new \Exception('File not found: ' . $filename);
        }

        $container = new Container();
        $container->loadJson($filename);
        $value = $container->get($key, $defaultValue);

        $this->write($value);
    }
}