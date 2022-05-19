<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WP_Ultimo\Dependencies\Carbon\Laravel;

use WP_Ultimo\Dependencies\Carbon\Carbon;
use WP_Ultimo\Dependencies\Carbon\CarbonImmutable;
use WP_Ultimo\Dependencies\Carbon\CarbonInterval;
use WP_Ultimo\Dependencies\Carbon\CarbonPeriod;
use WP_Ultimo\Dependencies\Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use WP_Ultimo\Dependencies\Illuminate\Events\Dispatcher;
use WP_Ultimo\Dependencies\Illuminate\Events\EventDispatcher;
use WP_Ultimo\Dependencies\Illuminate\Support\Carbon as IlluminateCarbon;
use WP_Ultimo\Dependencies\Illuminate\Support\Facades\Date;
use Throwable;
class ServiceProvider extends \WP_Ultimo\Dependencies\Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->updateLocale();
        if (!$this->app->bound('events')) {
            return;
        }
        $service = $this;
        $events = $this->app['events'];
        if ($this->isEventDispatcher($events)) {
            $events->listen(\class_exists('WP_Ultimo\\Dependencies\\Illuminate\\Foundation\\Events\\LocaleUpdated') ? 'Illuminate\\Foundation\\Events\\LocaleUpdated' : 'locale.changed', function () use($service) {
                $service->updateLocale();
            });
        }
    }
    public function updateLocale()
    {
        $app = $this->app && \method_exists($this->app, 'getLocale') ? $this->app : app('translator');
        $locale = $app->getLocale();
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);
        CarbonPeriod::setLocale($locale);
        CarbonInterval::setLocale($locale);
        if (\class_exists(IlluminateCarbon::class)) {
            IlluminateCarbon::setLocale($locale);
        }
        if (\class_exists(Date::class)) {
            try {
                $root = Date::getFacadeRoot();
                $root->setLocale($locale);
            } catch (Throwable $e) {
                // Non Carbon class in use in Date facade
            }
        }
    }
    public function register()
    {
        // Needed for Laravel < 5.3 compatibility
    }
    protected function isEventDispatcher($instance)
    {
        return $instance instanceof EventDispatcher || $instance instanceof Dispatcher || $instance instanceof DispatcherContract;
    }
}
