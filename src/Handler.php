<?php

namespace CoRex\Command;

class Handler
{
    const TITLE = 'CoRex Command.';
    private $arguments;
    private $component;
    private $command;
    private $isHelp;
    private $hideInternal;
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

        // Scan for internal commands.
        $this->hideInternal = !$showInternalCommands;
        $this->registerOnPath(__DIR__);
        $this->throughComposer = $throughComposer;
    }

    /**
     * Register command-class.
     *
     * @param string $class
     * @throws \Exception
     */
    public function register($class)
    {
        SignatureHandler::register($class, $this->hideInternal);
    }

    /**
     * Register all classes in path and sub-path.
     *
     * @param string $path
     */
    public function registerOnPath($path)
    {
        $path = str_replace('\\', '/', $path);
        if (strlen($path) > 0 && substr($path, -1) == '/') {
            $path = rtrim($path, '//');
        }
        if (!is_dir($path)) {
            return;
        }
        $files = scandir($path);
        if (count($files) == 0) {
            return;
        }
        $commandSuffix = 'Command.php';
        foreach ($files as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (substr($file, -strlen($commandSuffix)) == $commandSuffix) {
                $class = $this->extractFullClass($path . '/' . $file);
                if ($class != '') {
                    $this->register($class);
                }
            }
            if (is_dir($path . '/' . $file)) {
                $this->registerOnPath($path . '/' . $file);
            }
        }
    }

    /**
     * Execute command.
     *
     * @return boolean
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->component == '') {
            Console::header(self::TITLE);
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

        $signature = SignatureHandler::getSignature($this->component, $this->command);

        if ($signature === null) {
            if ($this->command != '') {
                Console::header(self::TITLE);
                Console::throwError(
                    'Command ' . $this->command . ' in component ' . $this->component . ' does not exist.'
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
        SignatureHandler::call($this->component, $this->command, $this->arguments, false, $this->throughComposer);

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
            if (!SignatureHandler::componentExist($component)) {
                Console::throwError('Component not found: ' . $component);
            }
        } else {
            Console::title('Available commands:');
        }
        $components = SignatureHandler::getComponents();
        if (count($components) > 0) {
            foreach ($components as $componentName) {
                if ($component != '' && $componentName != $component) {
                    continue;
                }
                if (!SignatureHandler::isComponentVisible($componentName)) {
                    continue;
                }
                Console::title('  ' . $componentName);
                $commands = SignatureHandler::getCommands($componentName);
                foreach ($commands as $command => $properties) {
                    if (!$properties['visible']) {
                        continue;
                    }
                    Console::write('    ' . $componentName . ':' . $command, $this->indentLength);
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
        Console::header(self::TITLE);
        if ($component == '') {
            Console::throwError('Component not specified.');
        }
        if (!SignatureHandler::componentExist($component)) {
            Console::throwError('Component not found: ' . $component);
        }
        if ($command == '') {
            Console::throwError('Command not specified.');
        }
        $signature = SignatureHandler::getSignature($component, $command);
        if ($signature === null) {
            Console::throwError('Command not found: ' . $command);
        }

        // Show header.
        Console::info($signature['description']);
        Console::writeln('');

        // Show usage.
        Console::title('Usage:');
        Console::write('  ' . $component . ':' . $command);
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
     * Extract full class.
     *
     * @param string $filename
     * @return string
     */
    private function extractFullClass($filename)
    {
        $result = '';
        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            $data = explode("\n", $data);
            if (count($data) > 0) {
                $namespace = '';
                $class = '';
                foreach ($data as $line) {
                    $line = str_replace('  ', ' ', $line);
                    if (substr($line, 0, 9) == 'namespace') {
                        $namespace = $this->getPart($line, 2, ' ');
                        $namespace = rtrim($namespace, ';');
                    }
                    if (substr($line, 0, 5) == 'class') {
                        $class = $this->getPart($line, 2, ' ');
                    }
                }
                if ($namespace != '' && $class != '') {
                    $result = $namespace . '\\' . $class;
                }
            }
        }
        return $result;
    }

    /**
     * Get part.
     *
     * @param string $data
     * @param integer $index
     * @param string $separator Trims data on $separator..
     * @return string
     */
    private function getPart($data, $index, $separator)
    {
        $data = trim($data, $separator) . $separator;
        if ($data != '') {
            $data = explode($separator, $data);
            if (isset($data[$index - 1])) {
                return $data[$index - 1];
            }
        }
        return '';
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
        }
        return [
            'component' => $component,
            'command' => $command
        ];
    }
}