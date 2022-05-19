<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\ApplicationInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Http\Client\DelegateHttpClient;
use WP_Ultimo\Dependencies\Amp\Http\Client\NetworkInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Promise;
abstract class ConditionalInterceptor implements ApplicationInterceptor, NetworkInterceptor
{
    private $interceptor;
    /**
     * @param ApplicationInterceptor|NetworkInterceptor $interceptor
     *
     * @throws \TypeError
     */
    public function __construct($interceptor)
    {
        if (!$interceptor instanceof ApplicationInterceptor && !$interceptor instanceof NetworkInterceptor) {
            throw new \TypeError('$interceptor must be an instance of ApplicationInterceptor or NetworkInterceptor');
        }
        $this->interceptor = $interceptor;
    }
    public final function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $next) : Promise
    {
        if ($this->interceptor instanceof ApplicationInterceptor && $this->matches($request)) {
            return $this->interceptor->request($request, $cancellation, $next);
        }
        return $next->request($request, $cancellation);
    }
    public final function requestViaNetwork(Request $request, CancellationToken $cancellation, Stream $stream) : Promise
    {
        if ($this->interceptor instanceof NetworkInterceptor && $this->matches($request)) {
            return $this->interceptor->requestViaNetwork($request, $cancellation, $stream);
        }
        return $stream->request($request, $cancellation);
    }
    protected abstract function matches(Request $request) : bool;
}
