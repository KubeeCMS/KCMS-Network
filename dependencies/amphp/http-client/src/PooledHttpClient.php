<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\ConnectionPool;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\DefaultConnectionPool;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\InterceptedStream;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor\DecompressResponse;
use WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor\SetRequestHeaderIfUnset;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidCloning;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidSerialization;
use WP_Ultimo\Dependencies\Amp\NullCancellationToken;
use WP_Ultimo\Dependencies\Amp\Promise;
use function WP_Ultimo\Dependencies\Amp\call;
final class PooledHttpClient implements HttpClient
{
    use ForbidCloning;
    use ForbidSerialization;
    /** @var ConnectionPool */
    private $connectionPool;
    /** @var NetworkInterceptor[] */
    private $networkInterceptors = [];
    /** @var NetworkInterceptor[] */
    private $defaultNetworkInterceptors;
    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? new DefaultConnectionPool();
        // We want to set these by default if the user doesn't choose otherwise
        $this->defaultNetworkInterceptors = [new SetRequestHeaderIfUnset('accept', '*/*'), new SetRequestHeaderIfUnset('user-agent', 'amphp/http-client @ v4.x'), new DecompressResponse()];
    }
    public function request(Request $request, ?CancellationToken $cancellation = null) : Promise
    {
        return call(function () use($request, $cancellation) {
            $request = clone $request;
            $cancellation = $cancellation ?? new NullCancellationToken();
            $stream = (yield $this->connectionPool->getStream($request, $cancellation));
            \assert($stream instanceof Stream);
            $networkInterceptors = \array_merge($this->defaultNetworkInterceptors, $this->networkInterceptors);
            foreach (\array_reverse($networkInterceptors) as $interceptor) {
                $stream = new InterceptedStream($stream, $interceptor);
            }
            return (yield $stream->request($request, $cancellation));
        });
    }
    /**
     * Adds a network interceptor.
     *
     * Network interceptors are only invoked if the request requires network access, i.e. there's no short-circuit by
     * an application interceptor, e.g. a cache.
     *
     * Whether the given network interceptor will be respected for currently running requests is undefined.
     *
     * Any new requests have to take the new interceptor into account.
     *
     * @param NetworkInterceptor $networkInterceptor
     *
     * @return self
     */
    public function intercept(NetworkInterceptor $networkInterceptor) : self
    {
        $clone = clone $this;
        $clone->networkInterceptors[] = $networkInterceptor;
        return $clone;
    }
}
