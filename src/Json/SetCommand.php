<?php

namespace CoRex\Command\Json;

use CoRex\Command\BaseCommand;
use CoRex\Support\Container;

class SetCommand extends BaseCommand
{
    protected $component = 'json';
    protected $signature = 'set
        {filename : Filename of json}
        {key : Key (dot notation supported)}
        {type : Type of value (int, string, json)}
        {value : Value (arrays supported)}';
    protected $description = 'Set key';
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
        $type = $this->argument('type');
        $value = $this->argument('value');

        if (!file_exists($filename)) {
            throw new \Exception('File not found: ' . $filename);
        }

        $container = new Container();
        $container->getJson($filename);

        // Validate type.
        if (!in_array($type, ['int', 'string', 'json'])) {
            throw new \Exception('Unknown type: ' . $type);
        }

        // Set value.
        if ($type == 'int') {
            $value = intval($value);
        } elseif ($type == 'string') {
            $value = (string)$value;
        } elseif ($type == 'json') {
            $value = json_decode($value, true);
            if (!is_array($value)) {
                throw new \Exception('Value is not json');
            }
        }
        $container->set($key, $value);

        $container->putJson($filename);

        $this->info('Key ' . $key . ' set.');
    }
}