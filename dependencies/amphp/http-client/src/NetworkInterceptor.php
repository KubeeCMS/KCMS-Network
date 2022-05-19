<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Promise;
/**
 * Allows intercepting an HTTP request after the connection to the remote server has been established.
 */
interface NetworkInterceptor
{
    /**
     * Intercepts an HTTP request after the connection to the remote server has been established.
     *
     * The implementation might modify the request and/or modify the response after the promise returned from
     * `$stream->request(...)` resolved.
     *
     * A NetworkInterceptor MUST NOT short-circuit and MUST delegate to the `$stream` passed as third argument exactly
     * once. The only exception to this rule is throwing an exception, e.g. because the TLS settings used are
     * unacceptable. If you need short circuits, use an {@see ApplicationInterceptor} instead.
     *
     * @param Request           $request
     * @param CancellationToken $cancellation
     * @param Stream            $stream
     *
     * @return Promise<Response>
     */
    public function requestViaNetwork(Request $request, CancellationToken $cancellation, Stream $stream) : Promise;
}
