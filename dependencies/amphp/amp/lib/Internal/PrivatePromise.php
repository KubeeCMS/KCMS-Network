<?php

namespace WP_Ultimo\Dependencies\Amp\Internal;

use WP_Ultimo\Dependencies\Amp\Promise;
/**
 * Wraps a Promise instance that has public methods to resolve and fail the promise into an object that only allows
 * access to the public API methods.
 */
final class PrivatePromise implements Promise
{
    /** @var Promise */
    private $promise;
    public function __construct(Promise $promise)
    {
        $this->promise = $promise;
    }
    public function onResolve(callable $onResolved)
    {
        $this->promise->onResolve($onResolved);
    }
}
