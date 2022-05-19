<?php

namespace WP_Ultimo\Dependencies\Amp\Cache;

use WP_Ultimo\Dependencies\Amp\Promise;
use WP_Ultimo\Dependencies\Amp\Success;
/**
 * Cache implementation that just ignores all operations and always resolves to `null`.
 */
class NullCache implements Cache
{
    /** @inheritdoc */
    public function get(string $key) : Promise
    {
        return new Success();
    }
    /** @inheritdoc */
    public function set(string $key, string $value, int $ttl = null) : Promise
    {
        /** @var Promise<void> */
        return new Success();
    }
    /** @inheritdoc */
    public function delete(string $key) : Promise
    {
        return new Success(\false);
    }
}
