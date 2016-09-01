<?php

namespace CoRex\Command;

class Style
{
    private static $styles = [
        'normal' => ['foreground' => 'white', 'background' => ''],
        'error' => ['foreground' => 'white', 'background' => 'red'],
        'warning' => ['foreground' => 'cyan', 'background' => ''],
        'info' => ['foreground' => 'green', 'background' => ''],
        'comment' => ['foreground' => 'green', 'background' => ''],
        'title' => ['foreground' => 'yellow', 'background' => '']
    ];

    /**
     * Set style.
     *
     * @param string $style
     * @param string $foreground
     * @param string $background
     */
    public static function setStyle($style, $foreground, $background)
    {
        self::$styles[$style] = [
            'foreground' => $foreground,
            'background' => $background
        ];
    }

    /**
     * Get foreground color.
     *
     * @param string $style
     * @return string
     */
    public static function getForeground($style)
    {
        return self::get($style, 'foreground');
    }

    /**
     * Get background color.
     *
     * @param string $style
     * @return string
     */
    public static function getBackground($style)
    {
        return self::get($style, 'background');
    }

    /**
     * Apply foreground and background color.
     *
     * @param string $text
     * @param string $foreground Default ''.
     * @param string $background Default ''.
     * @return string
     * @throws \Exception
     */
    public static function apply($text, $foreground = '', $background = '')
    {
        $style = new OutputFormatterStyle();
        if ($foreground != '') {
            $style->setForeground($foreground);
        }
        if ($background != '') {
            $style->setBackground($background);
        }
        return $style->apply($text);
    }

    /**
     * Apply style.
     *
     * @param string $text
     * @param string $style
     * @return string
     */
    public static function applyStyle($text, $style)
    {
        $foreground = self::getForeground($style);
        $background = self::getBackground($style);
        return self::apply($text, $foreground, $background);
    }

    /**
     * Get style setting.
     *
     * @param string $style
     * @param string $setting
     * @param string $defaultValue Default ''.
     * @return string
     */
    private static function get($style, $setting, $defaultValue = '')
    {
        if (isset(self::$styles[$style])) {
            $style = self::$styles[$style];
            if (isset($style[$setting])) {
                return $style[$setting];
            }
        }
        return $defaultValue;
    }
}