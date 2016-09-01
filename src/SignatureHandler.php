<?php

namespace CoRex\Command;

class SignatureHandler
{
    private static $commands;

    /**
     * Register command class.
     *
     * @param string $class
     * @param boolean $hideInternal
     * @throws \Exception
     */
    public static function register($class, $hideInternal)
    {
        self::initialize();

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
        if ($hideInternal && substr($class, 0, strlen($internalCommandPrefix)) == $internalCommandPrefix) {
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

        if (!isset(self::$commands[$component])) {
            self::$commands[$component] = [];
        }
        self::$commands[$component][$command] = [
            'class' => $class,
            'description' => $description,
            'visible' => $visible,
            'arguments' => $arguments,
            'options' => $options
        ];
    }

    /**
     * Get signature.
     *
     * @param string $component
     * @param string $command
     * @return array|null
     */
    public static function getSignature($component, $command)
    {
        self::initialize();
        $data = null;
        if (isset(self::$commands[$component][$command])) {
            $data = self::$commands[$component][$command];
        }
        return $data;
    }

    /**
     * Is component visible.
     *
     * @param string $component
     * @return boolean
     */
    public static function isComponentVisible($component)
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
    public static function call($component, $command, array $arguments = [], $silent = false, $throughComposer = false)
    {
        self::initialize();
        $signature = self::getSignature($component, $command);
        $class = $signature['class'];
        if (!class_exists($class)) {
            Console::throwError('Class ' . $class . ' does not exist.');
        }
        $object = new $class();
        $object->setProperties($signature, $arguments, $throughComposer);
        $object->setSilent($silent);
        $result = $object->run();
        $object->setSilent(false);
        return $result;
    }

    /**
     * Check if a component exists.
     *
     * @param string $component
     * @return boolean
     */
    public static function componentExist($component)
    {
        return isset(self::$commands[$component]);
    }

    /**
     * Check if a command exist.
     *
     * @param string $component
     * @param string $command
     * @return boolean
     */
    public static function commandExist($component, $command)
    {
        return isset(self::$commands[$component][$command]);
    }

    /**
     * Get components.
     *
     * @return array
     */
    public static function getComponents()
    {
        if (count(self::$commands) == 0) {
            return [];
        }
        $components = array_keys(self::$commands);
        sort($components);
        return $components;
    }

    /**
     * Get commands.
     *
     * @param string $component
     * @return array
     */
    public static function getCommands($component)
    {
        if (!isset(self::$commands[$component])) {
            return [];
        }
        $result = [];
        $commands = array_keys(self::$commands[$component]);
        sort($commands);
        foreach ($commands as $command) {
            $result[$command] = self::$commands[$component][$command];
        }
        return $result;
    }

    /**
     * Convert command to deep-array.
     *
     * @param string $command
     * @param array $data
     * @return array
     */
    private static function convertCommandToArray($command, array $data)
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
     * Initialize.
     */
    private static function initialize()
    {
        if (self::$commands === null) {
            self::$commands = [];
        }
    }
}