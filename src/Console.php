<?php

namespace CoRex\Command;

class Console
{
    private static $lineLength = 80;
    private static $silent = false;

    /**
     * Set silent.
     *
     * @param boolean $silent Default true.
     */
    public static function setSilent($silent = true)
    {
        self::$silent = $silent;
    }

    /**
     * Set length of line.
     *
     * @param integer $lineLength
     */
    public static function setLineLength($lineLength)
    {
        self::$lineLength = $lineLength;
    }

    /**
     * Write messages.
     *
     * @param string|array $messages
     * @param integer $length Default 0 which means not fixed length.
     * @param string $style Default '' which means 'normal'.
     * @param string $separator Default ''.
     * @throws \Exception
     */
    public static function write($messages, $length = 0, $style = '', $separator = '')
    {
        if (self::$silent) {
            return;
        }
        if (is_string($messages)) {
            $messages = [$messages];
        }
        if (count($messages) > 0) {
            foreach ($messages as $message) {
                if ($length > 0) {
                    $message = str_pad($message, $length, ' ', STR_PAD_RIGHT);
                }
                print(Style::applyStyle($message, $style));
                if ($separator != '') {
                    print($separator);
                }
            }
        }
    }

    /**
     * Write messages with linebreak.
     *
     * @param string|array $messages
     * @param integer $length Default 0 which means not fixed length.
     * @param string $style Default 'normal'.
     */
    public static function writeln($messages, $length = 0, $style = 'normal')
    {
        self::write($messages, $length, $style, "\n");
    }

    /**
     * Write header (title + separator).
     *
     * @param string $title
     */
    public static function header($title)
    {
        $title = str_pad($title, self::$lineLength, ' ', STR_PAD_RIGHT);
        self::writeln($title, 0, 'title');
        self::separator('=');
    }

    /**
     * Write separator-line.
     *
     * @param string $character Default '='.
     */
    public static function separator($character = '=')
    {
        self::writeln(str_repeat($character, self::$lineLength));
    }

    /**
     * Write info messages.
     *
     * @param string|array $messages
     * @param boolean $linebreak Default false.
     * @param integer $length Default 0 which means not fixed length.
     */
    public static function info($messages, $linebreak = true, $length = 0)
    {
        $separator = $linebreak ? "\n" : '';
        self::write($messages, $length, 'info', $separator);
    }

    /**
     * Write error messages.
     *
     * @param string|array $messages
     * @param boolean $linebreak Default false.
     * @param integer $length Default 0 which means not fixed length.
     */
    public static function error($messages, $linebreak = true, $length = 0)
    {
        $separator = $linebreak ? "\n" : '';
        self::write($messages, $length, 'error', $separator);
    }

    /**
     * Write comment messages.
     *
     * @param string|array $messages
     * @param boolean $linebreak Default false.
     * @param integer $length Default 0 which means not fixed length.
     */
    public static function comment($messages, $linebreak = true, $length = 0)
    {
        $separator = $linebreak ? "\n" : '';
        self::write($messages, $length, 'comment', $separator);
    }

    /**
     * Write warning messages.
     *
     * @param string|array $messages
     * @param boolean $linebreak Default false.
     * @param integer $length Default 0 which means not fixed length.
     */
    public static function warning($messages, $linebreak = true, $length = 0)
    {
        $separator = $linebreak ? "\n" : '';
        self::write($messages, $length, 'warning', $separator);
    }

    /**
     * Write title messages.
     *
     * @param string|array $messages
     * @param boolean $linebreak Default false.
     * @param integer $length Default 0 which means not fixed length.
     */
    public static function title($messages, $linebreak = true, $length = 0)
    {
        $separator = $linebreak ? "\n" : '';
        self::write($messages, $length, 'title', $separator);
    }

    /**
     * Write block messages.
     *
     * @param string|array $messages
     * @param string $style
     */
    public static function block($messages, $style)
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }
        if (count($messages) > 0) {
            self::writeln(str_repeat(' ', self::$lineLength), 0, $style);
            foreach ($messages as $message) {
                $message = ' ' . $message;
                while (strlen($message) < self::$lineLength) {
                    $message .= ' ';
                }
                self::writeln($message, 0, $style);
            }
            self::writeln(str_repeat(' ', self::$lineLength), 0, $style);
        }
    }

    /**
     * Ask question.
     *
     * @param string $question
     * @param mixed $defaultValue Default null.
     * @param boolean $secret Default false.
     * @return string
     */
    public static function ask($question, $defaultValue = null, $secret = false)
    {
        $value = '';
        while (trim($value) == '') {
            self::writeln('');
            self::write(' ' . $question, 0, 'info');
            if ($defaultValue !== null) {
                self::write(' [');
                self::write($defaultValue, 0, 'comment');
                self::write(']');
            }
            self::writeln(':');
            if ($secret) {
                self::write(' > ');
                system('stty -echo');
                $value = trim(fgets(STDIN));
                system('stty echo');
            } else {
                $value = readline(' > ');
            }
            if (trim($value) == '') {
                $value = $defaultValue;
            }
            if (trim($value) == '') {
                self::writeln('');
                self::block('[ERROR] A value is required', 'error');
            }
            self::writeln('');
        }
        return trim($value);
    }

    /**
     * Confirm question.
     *
     * @param string $question
     * @param boolean $defaultValue Default false.
     * @return string
     */
    public static function confirm($question, $defaultValue = false)
    {
        $value = $defaultValue ? 'yes' : 'no';
        $value = self::ask($question . ' (yes/no)', $value);
        if (substr($value, 0, 1) == 'y') {
            $value = 'yes';
        } else {
            $value = 'no';
        }
        return $value;
    }

    /**
     * Ask for secret.
     *
     * @param string $question
     * @return string
     */
    public static function secret($question)
    {
        return self::ask($question, null, true);
    }

    /**
     * List choices and ask for choice.
     *
     * @param string $question
     * @param array $choices
     * @param mixed $defaultValue Default null.
     * @return string
     */
    public static function choice($question, array $choices, $defaultValue = null)
    {
        $value = '';
        while (trim($value) == '') {

            // Write prompt.
            self::writeln('');
            self::write(' ' . $question, 0, 'info');
            if ($defaultValue !== null) {
                self::write(' [');
                self::write((string)$defaultValue, 0, 'comment');
                self::write(']');
            }
            self::writeln(':');

            // Write choices.
            if (count($choices) > 0) {
                foreach ($choices as $index => $choice) {
                    self::write('  [');
                    self::write((string)$index, 0, 'comment');
                    self::writeln('] ' . $choice);
                }
            }

            // Input.
            $value = readline(' > ');
            if (trim($value) == '') {
                $value = $defaultValue;
            }
            if (!in_array(intval($value), array_keys($choices))) {
                self::writeln('');
                self::block('[ERROR] Value "' . $value . '" is invalid', 'error');
                $value = '';
            } elseif (trim($value) == '') {
                self::writeln('');
                self::block('[ERROR] A value is required', 'error');
            }
            self::writeln('');
        }
        return trim($value);
    }

    /**
     * Show table.
     *
     * @param array $headers
     * @param array $rows
     */
    public static function table(array $headers, array $rows)
    {
        $table = new Table();
        $table->setHeaders($headers);
        $table->setRows($rows);
        $output = $table->render();
        self::writeln($output);
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
        self::write(implode($separator, $words), 0, $style);
    }

    /**
     * Throw error-message as exception.
     *
     * @param string $message
     * @throws \Exception
     */
    public static function throwError($message)
    {
        throw new \Exception(Style::applyStyle($message, 'error'));
    }
}