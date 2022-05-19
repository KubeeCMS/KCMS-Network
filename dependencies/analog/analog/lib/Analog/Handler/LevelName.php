<?php

namespace WP_Ultimo\Dependencies\Analog\Handler;

/**
 * Translates log level codes to their names
 *
 *
 * Usage:
 *
 *     // The log level (3rd value) must be formatted as a string
 *     Analog::$format = "%s - %s - %s - %s\n";
 * 
 *     Analog::handler (Analog\Handler\LevelName::init (
 *         Analog\Handler\File::init ($file)
 *     ));
 */
class LevelName
{
    /**
     * Translation list for log levels.
     */
    private static $log_levels = array(\WP_Ultimo\Dependencies\Analog\Analog::DEBUG => 'DEBUG', \WP_Ultimo\Dependencies\Analog\Analog::INFO => 'INFO', \WP_Ultimo\Dependencies\Analog\Analog::NOTICE => 'NOTICE', \WP_Ultimo\Dependencies\Analog\Analog::WARNING => 'WARNING', \WP_Ultimo\Dependencies\Analog\Analog::ERROR => 'ERROR', \WP_Ultimo\Dependencies\Analog\Analog::CRITICAL => 'CRITICAL', \WP_Ultimo\Dependencies\Analog\Analog::ALERT => 'ALERT', \WP_Ultimo\Dependencies\Analog\Analog::URGENT => 'URGENT');
    /**
     * This contains the handler to send to
     */
    public static $handler;
    public static function init($handler)
    {
        self::$handler = $handler;
        return function ($info) {
            if (isset(self::$log_levels[$info['level']])) {
                $info['level'] = self::$log_levels[$info['level']];
            }
            $handler = LevelName::$handler;
            $handler($info);
        };
    }
}
