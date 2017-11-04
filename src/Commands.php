<?php

namespace CoRex\Command;

use CoRex\Support\System\Console;

class Commands
{
    private static $instance;
    private $commands;
    private $visible;
    private $hideInternal;

    /**
     * Commands constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Get instance.
     *
     * @return Commands
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Clear.
     */
    public function clear()
    {
        $this->commands = [];
        $this->visible = [];
        $this->hideInternal = false;
    }

    /**
     * Hide internal.
     *
     * @param boolean $hideInternal
     */
    public function hideInternal($hideInternal)
    {
        $this->hideInternal = $hideInternal;
    }

    /**
     * Register command class.
     *
     * @param string $class
     * @throws \Exception
     */
    public function register($class)
    {
        // Check if $hideInternal is set.
        if ($this->hideInternal === null) {
            Console::throwError(__CLASS__ . '::hideInternal() must be called prior to register any commands.');
        }

        if (!is_string($class)) {
            Console::throwError('You must specify name of class i.e. MyClass::class');
        }
        if (!class_exists($class)) {
            Console::throwError('Class ' . $class . ' does not exist.');
        }

        // Get properties from class.
        $reflectionClass = new \ReflectionClass($class);
        $properties = $reflectionClass->getDefaultProperties();
        if (!isset($properties['component'])) {
            Console::throwError('$component not found in ' . $class);
        }
        if (!isset($properties['signature'])) {
            Console::throwError('$signature not found in ' . $class);
        }
        if (!isset($properties['description'])) {
            Console::throwError('$description not found in ' . $class);
        }
        if (!isset($properties['visible'])) {
            Console::throwError('$visible not found in ' . $class);
        }
        $component = $properties['component'];
        $signature = $properties['signature'];
        $description = $properties['description'];
        $visible = $properties['visible'];

        // Hide internal command if needed.
        $internalCommandPrefix = 'CoRex\Command\\';
        if ($this->hideInternal && substr($class, 0, strlen($internalCommandPrefix)) == $internalCommandPrefix) {
            $visible = false;
        }

        // Extract command.
        $command = $signature;
        if (strpos($command, '{') > 0) {
            $command = substr($command, 0, strpos($command, '{'));
        }
        $command = trim($command);

        // Unpack signature.
        $arguments = [];
        $options = [];
        preg_match_all('/\{([^\}]+)\}/', $signature, $matchArguments);
        if (count($matchArguments[1]) > 0) {
            foreach ($matchArguments[1] as $argument) {

                // Unpack argument.
                $argument = explode(':', $argument);
                $argumentKey = trim($argument[0]);
                $argumentValue = '';
                if (isset($argument[1])) {
                    $argumentValue = trim($argument[1]);
                }

                // Check if optional.
                $optional = false;
                if (substr($argumentKey, -1) == '?') {
                    $argumentKey = substr($argumentKey, 0, -1);
                    $optional = true;
                }

                // Check if value is required.
                $hasValue = false;
                if (substr($argumentKey, -1) == '=') {
                    $argumentKey = substr($argumentKey, 0, -1);
                    $hasValue = true;
                }

                // Set argument/option.
                if (substr($argumentKey, 0, 2) != '--') {
                    $arguments[$argumentKey] = [
                        'description' => $argumentValue,
                        'optional' => $optional
                    ];
                } else {
                    $argumentKey = substr($argumentKey, 2);
                    $options[$argumentKey] = [
                        'description' => $argumentValue,
                        'hasValue' => $hasValue
                    ];
                }
            }
        }

        if (!isset($this->commands[$component])) {
            $this->commands[$component] = [];
        }
        $this->commands[$component][$command] = [
            'class' => $class,
            'description' => $description,
            'visible' => $visible,
            'arguments' => $arguments,
            'options' => $options
        ];
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
                $class = self::extractFullClass($path . '/' . $file);
                if ($class != '') {
                    self::register($class);
                }
            }
            if (is_dir($path . '/' . $file)) {
                self::registerOnPath($path . '/' . $file);
            }
        }
    }

    /**
     * Get signature.
     *
     * @param string $component
     * @param string $command
     * @return array|null
     */
    public function getSignature($component, $command)
    {
        $data = null;
        if (isset($this->commands[$component][$command])) {
            $data = $this->commands[$component][$command];
        }
        return $data;
    }

    /**
     * Is component visible.
     *
     * @param string $component
     * @return boolean
     */
    public function isComponentVisible($component)
    {
        $result = false;
        $commands = self::getCommands($component);
        if (count($commands) > 0) {
            foreach ($commands as $command => $properties) {
                if ($properties['visible']) {
                    $result = true;
                }
            }
        }

        // Check if visibility is overridden.
        if (isset($this->visible[$component]['*']) && !$this->visible[$component]['*']) {
            $result = false;
        }

        return $result;
    }

    /**
     * Call command.
     *
     * @param string $component
     * @param string $command
     * @param array $arguments
     * @param boolean $silent
     * @param boolean $throughComposer
     * @return mixed
     * @throws \Exception
     */
    public function call($component, $command, array $arguments = [], $silent = false, $throughComposer = false)
    {
        $signature = self::getSignature($component, $command);
        if ($signature === null) {
            Console::throwError('Command not found.');
        }
        $class = $signature['class'];
        if (!class_exists($class)) {
            Console::throwError('Class ' . $class . ' does not exist.');
        }
        $object = self::newCommand($class);
        $object->setProperties($signature, $arguments, $throughComposer);
        $object->setSilent($silent);
        if ($silent) {
            ob_start();
        }
        $result = $object->run();
        if ($silent) {
            ob_end_clean();
        }
        return $result;
    }

    /**
     * Check if a component exists.
     *
     * @param string $component
     * @return boolean
     */
    public function componentExist($component)
    {
        return isset($this->commands[$component]);
    }

    /**
     * Check if a command exist.
     *
     * @param string $component
     * @param string $command
     * @return boolean
     */
    public function commandExist($component, $command)
    {
        return isset($this->commands[$component][$command]);
    }

    /**
     * Get components.
     *
     * @return array
     */
    public function getComponents()
    {
        if (count($this->commands) == 0) {
            return [];
        }
        $components = array_keys($this->commands);
        sort($components);
        return $components;
    }

    /**
     * Get commands.
     *
     * @param string $component
     * @return array
     */
    public function getCommands($component)
    {
        if (!isset($this->commands[$component])) {
            return [];
        }
        $result = [];
        $commands = array_keys($this->commands[$component]);
        sort($commands);
        foreach ($commands as $command) {

            // Check if visibility is overridden.
            if (isset($this->visible[$component][$command]) && !$this->visible[$component][$command]) {
                continue;
            }

            $result[$command] = $this->commands[$component][$command];
        }
        return $result;
    }

    /**
     * Set component visibility.
     *
     * @param string $component
     * @param boolean $visible
     */
    public function setComponentVisibility($component, $visible)
    {
        $this->visible[$component]['*'] = $visible;
    }

    /**
     * Hide component.
     *
     * @param string $component
     */
    public function hideComponent($component)
    {
        self::setComponentVisibility($component, false);
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
        $this->visible[$component][$command] = $visible;
    }

    /**
     * Hide command.
     *
     * @param string $component
     * @param string $command
     */
    public function hideCommand($component, $command)
    {
        self::setCommandVisibility($component, $command, false);
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
        if (!is_array($commands)) {
            throw new \Exception('Specified commands parameter is not an array.');
        }
        foreach ($commands as $command) {
            self::hideCommand($component, $command);
        }
    }

    /**
     * Convert command to deep-array.
     *
     * @param string $command
     * @param array $data
     * @return array
     */
    private function convertCommandToArray($command, array $data)
    {
        if ($command == '') {
            return [];
        }

        $commandParts = explode(':', $command);
        $part = $commandParts[0];
        unset($commandParts[0]);
        $command = implode(':', $commandParts);

        if ($command != '') {
            $result[$part] = self::convertCommandToArray($command, $data);
        } else {
            $result[$part] = $data;
        }

        return $result;
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
            $data = self::getFileContent($filename);
            $data = explode("\n", $data);
            if (count($data) > 0) {
                $namespace = '';
                $class = '';
                foreach ($data as $line) {
                    $line = str_replace('  ', ' ', $line);
                    if (substr($line, 0, 9) == 'namespace') {
                        $namespace = self::getPart($line, 2, ' ');
                        $namespace = rtrim($namespace, ';');
                    }
                    if (substr($line, 0, 5) == 'class') {
                        $class = self::getPart($line, 2, ' ');
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
     * Get file content.
     *
     * @param string $filename
     * @return string
     */
    private function getFileContent($filename)
    {
        $content = '';
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = str_replace("\r", '', $content);
        }
        return $content;
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
     * New command.
     *
     * @param string $commandClass
     * @return BaseCommand
     */
    private function newCommand($commandClass)
    {
        return new $commandClass();
    }
}