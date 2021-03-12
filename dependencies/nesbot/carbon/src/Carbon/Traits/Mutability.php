<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WP_Ultimo\Dependencies\Carbon\Traits;

use WP_Ultimo\Dependencies\Carbon\Carbon;
use WP_Ultimo\Dependencies\Carbon\CarbonImmutable;
/**
 * Trait Mutability.
 *
 * Utils to know if the current object is mutable or immutable and convert it.
 */
trait Mutability
{
    use Cast;
    /**
     * Returns true if the current class/instance is mutable.
     *
     * @return bool
     */
    public static function isMutable()
    {
        return \false;
    }
    /**
     * Returns true if the current class/instance is immutable.
     *
     * @return bool
     */
    public static function isImmutable()
    {
        return !static::isMutable();
    }
    /**
     * Return a mutable copy of the instance.
     *
     * @return Carbon
     */
    public function toMutable()
    {
        /** @var Carbon $date */
        $date = $this->cast(\WP_Ultimo\Dependencies\Carbon\Carbon::class);
        return $date;
    }
    /**
     * Return a immutable copy of the instance.
     *
     * @return CarbonImmutable
     */
    public function toImmutable()
    {
        /** @var CarbonImmutable $date */
        $date = $this->cast(\WP_Ultimo\Dependencies\Carbon\CarbonImmutable::class);
        return $date;
    }
}
