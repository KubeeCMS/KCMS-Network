<?php

namespace WP_Ultimo\Dependencies\React\EventLoop;

/**
 * The `Factory` class exists as a convenient way to pick the best available event loop implementation.
 */
final class Factory
{
    /**
     * Creates a new event loop instance
     *
     * ```php
     * $loop = React\EventLoop\Factory::create();
     * ```
     *
     * This method always returns an instance implementing `LoopInterface`,
     * the actual event loop implementation is an implementation detail.
     *
     * This method should usually only be called once at the beginning of the program.
     *
     * @return LoopInterface
     */
    public static function create()
    {
        // @codeCoverageIgnoreStart
        if (\function_exists('uv_loop_new')) {
            // only use ext-uv on PHP 7
            return new \WP_Ultimo\Dependencies\React\EventLoop\ExtUvLoop();
        } elseif (\class_exists('WP_Ultimo\\Dependencies\\libev\\EventLoop', \false)) {
            return new \WP_Ultimo\Dependencies\React\EventLoop\ExtLibevLoop();
        } elseif (\class_exists('EvLoop', \false)) {
            return new \WP_Ultimo\Dependencies\React\EventLoop\ExtEvLoop();
        } elseif (\class_exists('EventBase', \false)) {
            return new \WP_Ultimo\Dependencies\React\EventLoop\ExtEventLoop();
        } elseif (\function_exists('event_base_new') && \PHP_MAJOR_VERSION === 5) {
            // only use ext-libevent on PHP 5 for now
            return new \WP_Ultimo\Dependencies\React\EventLoop\ExtLibeventLoop();
        }
        return new \WP_Ultimo\Dependencies\React\EventLoop\StreamSelectLoop();
        // @codeCoverageIgnoreEnd
    }
}
