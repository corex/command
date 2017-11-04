<?php

namespace CoRex\Command;

use CoRex\Support\System\Console;

class Handler
{
    private $title = 'CoRex Command.';
    private $arguments;
    private $component;
    private $command;
    private $isHelp;
    private $throughComposer;
    private $indentLength;

    /**
     * Arguments from CLI.
     *
     * Handler constructor.
     * @param array $arguments
     * @param boolean $showInternalCommands
     * @param boolean $throughComposer
     * @param integer $indentLength Default 30.
     */
    public function __construct(array $arguments, $showInternalCommands, $throughComposer, $indentLength = 30)
    {
        $this->indentLength = $indentLength;
        if (isset($arguments[0])) {
            unset($arguments[0]);
            $arguments = array_values($arguments);
        }
        $this->component = '';
        $this->command = '';
        $this->isHelp = false;
        if (isset($arguments[0]) && strtolower($arguments[0]) == 'help') {
            $this->isHelp = true;
            unset($arguments[0]);
            $arguments = array_values($arguments);
        }
        $componentCommand = '';
        if (isset($arguments[0])) {
            $componentCommand = $arguments[0];
            unset($arguments[0]);
        }
        $argumentParts = $this->splitArgument($componentCommand);
        $this->component = $argumentParts['component'];
        $this->command = $argumentParts['command'];
        $this->arguments = array_values($arguments);

        Commands::getInstance()->hideInternal(!$showInternalCommands);

        // Register internal commands.
        $this->registerOnPath(__DIR__);
        $this->throughComposer = $throughComposer;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Register command-class.
     *
     * @param string $class
     * @throws \Exception
     */
    public function register($class)
    {
        Commands::getInstance()->register($class);
    }

    /**
     * Register all classes in path and sub-path.
     *
     * @param string $path
     */
    public function registerOnPath($path)
    {
        Commands::getInstance()->registerOnPath($path);
    }

    /**
     * Execute command.
     *
     * @param string $component Default ''.
     * @param string $command Default ''.
     * @return boolean
     * @throws \Exception
     */
    public function execute($component = '', $command = '')
    {
        if ($component != '' && $command != '') {
            $this->component = $component;
            $this->command = $command;
        }
        $signature = Commands::getInstance()->getSignature($this->component, $this->command);

        if ($this->component == '' && $signature === null) {
            Console::header($this->title);
            Console::title('Usage:');
            Console::writeln('  {component} {command} [options] [arguments]');
            Console::writeln('');
            Console::writeln('  To show help for command: help {component} {command}');
            Console::writeln('');
        }

        if ($this->isHelp) {
            $this->show($this->component, $this->command);
            return false;
        }

        if ($signature === null) {
            if ($this->command != '') {
                Console::header($this->title);
                $command = '';
                if ($this->component != '') {
                    $command .= $this->component . ':';
                }
                $command .= $this->command;
                Console::throwError(
                    'Command ' . $command . ' does not exist.'
                );
            }
            $this->showAll($this->component);
            return false;
        }

        // Execute command.
        $class = $signature['class'];
        if (!in_array('setProperties', get_class_methods($class))) {
            Console::throwError($class . ' does not extend CoRex\Command\BaseCommand.');
        }
        Commands::getInstance()->call(
            $this->component,
            $this->command,
            $this->arguments,
            false,
            $this->throughComposer
        );

        return true;
    }

    /**
     * Show all commands.
     *
     * @param string $component
     * @throws \Exception
     */
    public function showAll($component = '')
    {
        if ($component != '') {
            if (!Commands::getInstance()->componentExist($component)) {
                Console::throwError('Component not found: ' . $component);
            }
        } else {
            Console::title('Available commands:');
        }
        $components = Commands::getInstance()->getComponents();
        if (count($components) > 0) {
            foreach ($components as $componentName) {
                if ($component != '' && $componentName != $component) {
                    continue;
                }
                if (!Commands::getInstance()->isComponentVisible($componentName)) {
                    continue;
                }
                Console::title('  ' . $componentName);
                $commands = Commands::getInstance()->getCommands($componentName);
                foreach ($commands as $command => $properties) {
                    if (!$properties['visible']) {
                        continue;
                    }
                    if ($componentName != '') {
                        Console::write('    ' . $componentName . ':' . $command, '', $this->indentLength);
                    } else {
                        Console::write('  ' . $command, '', $this->indentLength);
                    }
                    Console::writeln($properties['description']);
                }
            }
        }
    }

    /**
     * Show command.
     *
     * @param string $component
     * @param string $command
     * @throws \Exception
     */
    public function show($component, $command)
    {
        Console::header($this->title);
        if (!Commands::getInstance()->componentExist($component)) {
            Console::throwError('Component not found: ' . $component);
        }
        if ($command == '') {
            Console::throwError('Command not specified.');
        }
        $signature = Commands::getInstance()->getSignature($component, $command);
        if ($signature === null) {
            Console::throwError('Command not found: ' . $command);
        }

        // Show header.
        Console::info($signature['description']);
        Console::writeln('');

        // Show usage.
        Console::title('Usage:');
        Console::write('  ');
        if ($component != '') {
            Console::write($component . ':');
        }
        Console::write($command);
        if (isset($signature['options']) && count($signature['options']) > 0) {
            Console::write(' [options]');
        }
        if (isset($signature['arguments']) && count($signature['arguments']) > 0) {
            Console::write(' [arguments]');
        }
        Console::writeln('');
        Console::writeln('');

        // Show options.
        if (isset($signature['options']) && count($signature['options']) > 0) {
            Console::title('Options:');
            foreach ($signature['options'] as $option => $properties) {
                $description = $properties['description'];
                if ($properties['hasValue']) {
                    $option .= '=';
                }
                Console::info('    --' . $option, false, $this->indentLength);
                if ($properties['hasValue']) {
                    Console::warning('(value) ', false);
                }
                Console::writeln($description);
            }
            Console::writeln('');
        }

        // Show arguments.
        if (isset($signature['arguments']) && count($signature['arguments']) > 0) {
            Console::title('Arguments:');
            foreach ($signature['arguments'] as $argument => $properties) {
                $description = $properties['description'];
                Console::info('    ' . $argument, false, $this->indentLength);
                if ($properties['optional']) {
                    Console::warning('(optional) ', false);
                }
                Console::writeln($description);
            }
            Console::writeln('');
        }
    }

    /**
     * Set component visibility.
     *
     * @param string $component
     * @param boolean $visible
     */
    public function setComponentVisibility($component, $visible)
    {
        Commands::getInstance()->setComponentVisibility($component, $visible);
    }

    /**
     * Hide component.
     *
     * @param string $component
     */
    public function hideComponent($component)
    {
        Commands::getInstance()->hideComponent($component);
    }

    /**
     * Set command visibility.
     *
     * @param string $component
     * @param string $command
     * @param boolean $visible
     */
    public function setCommandVisibility($component, $command, $visible)
    {
        Commands::getInstance()->setCommandVisibility($component, $command, $visible);
    }

    /**
     * Hide command.
     *
     * @param string $component
     * @param string $command
     */
    public function hideCommand($component, $command)
    {
        Commands::getInstance()->hideCommand($component, $command);
    }

    /**
     * Hide commands.
     *
     * @param string $component
     * @param array $commands
     * @throws \Exception
     */
    public function hideCommands($component, array $commands)
    {
        Commands::getInstance()->hideCommands($component, $commands);
    }

    /**
     * Split argument into parts.
     *
     * @param string $argument
     * @return array
     */
    private function splitArgument($argument)
    {
        $component = '';
        $command = '';
        if ($argument != '') {
            $argument = explode(':', strtolower($argument));
            $component = $argument[0];
            $command = isset($argument[1]) ? $argument[1] : '';
            if ($component != '' && $command == '') {
                $command = $component;
                $component = '';
            }
        }
        return [
            'component' => $component,
            'command' => $command
        ];
    }
}