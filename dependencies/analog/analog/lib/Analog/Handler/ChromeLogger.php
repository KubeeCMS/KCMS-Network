<?php

namespace WP_Ultimo\Dependencies\Analog\Handler;

require_once __DIR__ . '/../../ChromePhp.php';
/**
 * Log to the [Chrome Logger](http://craig.is/writing/chrome-logger).
 * Based on the [ChromePhp library](https://github.com/ccampbell/chromephp).
 *
 * Usage:
 *
 *     Analog::handler (Analog\Handler\ChromeLogger::init ());
 *     
 *     // send a debug message
 *     Analog::debug ($an_object);
 *
 *     // send an ordinary message
 *     Analog::info ('An error message');
 */
class ChromeLogger
{
    public static function init()
    {
        return function ($info) {
            switch ($info['level']) {
                case \WP_Ultimo\Dependencies\Analog\Analog::DEBUG:
                    \WP_Ultimo\Dependencies\ChromePhp::log($info['message']);
                    break;
                case \WP_Ultimo\Dependencies\Analog\Analog::INFO:
                case \WP_Ultimo\Dependencies\Analog\Analog::NOTICE:
                    \WP_Ultimo\Dependencies\ChromePhp::info($info['message']);
                    break;
                case \WP_Ultimo\Dependencies\Analog\Analog::WARNING:
                    \WP_Ultimo\Dependencies\ChromePhp::warn($info['message']);
                    break;
                case \WP_Ultimo\Dependencies\Analog\Analog::ERROR:
                case \WP_Ultimo\Dependencies\Analog\Analog::CRITICAL:
                case \WP_Ultimo\Dependencies\Analog\Analog::ALERT:
                case \WP_Ultimo\Dependencies\Analog\Analog::URGENT:
                    \WP_Ultimo\Dependencies\ChromePhp::error($info['message']);
                    break;
            }
        };
    }
}
