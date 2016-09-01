<?php

namespace CoRex\Command;

abstract class BaseCommand implements BaseCommandInterface
{
    private $signature;
    private $arguments;
    private $options;
    private $throughComposer;

    /**
     * Set arguments.
     *
     * @param array $signature
     * @param array $arguments
     * @param boolean $throughComposer
     * @throws \Exception
     */
    public function setProperties(array $signature, array $arguments, $throughComposer)
    {
        $this->signature = $signature;

        // Prepare arguments/options for extraction.
        $tempArguments = [];
        $tempOptions = [];
        if (count($arguments) > 0) {
            foreach ($arguments as $argument) {
                if (substr($argument, 0, 2) != '--') {
                    $tempArguments[] = $argument;
                } else {
                    $argument = explode('=', $argument);
                    $value = isset($argument[1]) ? $argument[1] : null;
                    $argument = substr($argument[0], 2);
                    $tempOptions[$argument] = $value;
                }
            }
        }

        // Extract arguments.
        $this->arguments = [];
        $argumentNumber = 0;
        foreach ($this->signature['arguments'] as $argument => $properties) {
            $value = null;
            if (isset($tempArguments[$argumentNumber])) {
                $value = $tempArguments[$argumentNumber];
            }
            $this->arguments[$argument] = $value;
            if ($this->arguments[$argument] === null && !$properties['optional']) {
                throw new \Exception('Argument required: ' . $argument);
            }
            $argumentNumber++;
        }

        // Extract options.
        $this->options = [];
        foreach ($this->signature['options'] as $option => $properties) {
            $value = $properties['hasValue'] ? null : false;
            if (in_array($option, array_keys($tempOptions))) {
                $value = true;
                if ($properties['hasValue']) {
                    $value = $tempOptions[$option];
                    if ($value === null) {
                        throw new \Exception('You must specify a value for ' . $option);
                    }
                }
            }
            $this->options[$option] = $value;
        }

        $this->throughComposer = $throughComposer;
    }

    /**
     * Set silent.
     *
     * @param boolean $silent Default true.
     */
    public function setSilent($silent = true)
    {
        Console::setSilent($silent);
    }

    /**
     * Return true false if installed through composer.
     */
    public function throughComposer()
    {
        return $this->throughComposer;
    }

    /**
     * Get argument.
     *
     * @param string $argument
     * @param mixed $defaultValue Default null.
     * @return mixed
     */
    public function argument($argument, $defaultValue = null)
    {
        if (isset($this->arguments[$argument])) {
            return $this->arguments[$argument];
        }
        return $defaultValue;
    }

    /**
     * Get option.
     *
     * @param string $option
     * @param mixed $defaultValue Default null.
     * @return mixed
     */
    public function option($option, $defaultValue = null)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return $defaultValue;
    }

    /**
     * Set length of line.
     *
     * @param integer $lineLength
     */
    public function setLineLength($lineLength)
    {
        Console::setLineLength($lineLength);
    }

    /**
     * Call command.
     *
     * @param string $component
     * @param string $command
     * @param array $arguments
     * @param boolean $silent
     * @return mixed
     * @throws \Exception
     */
    public function call($component, $command, array $arguments = [], $silent = false)
    {
        return SignatureHandler::call($component, $command, $arguments, $silent);
    }

    /**
     * Call command silently.
     *
     * @param string $command
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function callSilent($command, array $arguments = [])
    {
        return SignatureHandler::call($command, $arguments, true);
    }

    /**
     * Write messages.
     *
     * @param string|array $messages
     * @param string $style Default '' which means 'normal'.
     * @param boolean $linebreak Default false.
     * @throws \Exception
     */
    public function write($messages, $style = '', $linebreak = false)
    {
        Console::write($messages, $style, $linebreak);
    }

    /**
     * Write messages with linebreak.
     *
     * @param string|array $messages
     * @param string $style Default 'normal'.
     */
    public function writeln($messages, $style = 'normal')
    {
        Console::writeln($messages, $style);
    }

    /**
     * Write header (title + separator).
     *
     * @param string $title
     * @param string $style Default ''.
     */
    public function header($title, $style = '')
    {
        Console::header($title, $style);
    }

    /**
     * Write separator-line.
     *
     * @param string $character Default '='.
     */
    public function separator($character = '=')
    {
        Console::separator($character);
    }

    /**
     * Write info messages.
     *
     * @param string|array $messages
     */
    public function info($messages)
    {
        Console::info($messages);
    }

    /**
     * Write error messages.
     *
     * @param string|array $messages
     */
    public function error($messages)
    {
        Console::error($messages);
    }

    /**
     * Write comment messages.
     *
     * @param string|array $messages
     */
    public function comment($messages)
    {
        Console::comment($messages);
    }

    /**
     * Write warning messages.
     *
     * @param string|array $messages
     */
    public function warning($messages)
    {
        Console::warning($messages);
    }

    /**
     * Write title messages.
     *
     * @param string|array $messages
     */
    public function title($messages)
    {
        Console::title($messages);
    }

    /**
     * Write block messages.
     *
     * @param string|array $messages
     * @param string $style
     */
    public function block($messages, $style)
    {
        Console::block($messages, $style);
    }

    /**
     * Ask question.
     *
     * @param string $question
     * @param mixed $defaultValue Default null.
     * @param boolean $secret Default false.
     * @return string
     */
    public function ask($question, $defaultValue = null, $secret = false)
    {
        return Console::ask($question, $defaultValue, $secret);
    }

    /**
     * Confirm question.
     *
     * @param string $question
     * @param boolean $defaultValue Default false.
     * @return string
     */
    public function confirm($question, $defaultValue = false)
    {
        return Console::confirm($question, $defaultValue);
    }

    /**
     * Ask for secret.
     *
     * @param string $question
     * @return string
     */
    public function secret($question)
    {
        return Console::secret($question);
    }

    /**
     * List choices and ask for choice.
     *
     * @param string $question
     * @param array $choices
     * @param mixed $defaultValue Default null.
     * @return string
     */
    public function choice($question, array $choices, $defaultValue = null)
    {
        return Console::choice($question, $choices, $defaultValue);
    }

    /**
     * Show table.
     *
     * @param array $headers
     * @param array $rows
     */
    public function table(array $headers, array $rows)
    {
        Console::table($headers, $rows);
    }

    /**
     * Write words.
     *
     * @param array $words
     * @param string $style Default ''.
     * @param string $separator Default ', '.
     */
    public static function words(array $words, $style = '', $separator = ', ')
    {
        Console::words($words, $style, $separator);
    }
}