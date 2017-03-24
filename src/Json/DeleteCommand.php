<?php

namespace CoRex\Command\Json;

use CoRex\Command\BaseCommand;
use CoRex\Support\Container;

class DeleteCommand extends BaseCommand
{
    protected $component = 'json';
    protected $signature = 'delete
        {filename : Filename of json}
        {key : Key (dot notation supported)}';
    protected $description = 'Delete key';
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

        if (!file_exists($filename)) {
            throw new \Exception('File not found: ' . $filename);
        }

        $container = new Container();
        $container->getJson($filename);
        $container->delete($key);
        $container->putJson($filename);

        $this->info('Key ' . $key . ' removed.');
    }
}