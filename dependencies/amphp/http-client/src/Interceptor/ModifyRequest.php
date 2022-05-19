<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\ApplicationInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Http\Client\DelegateHttpClient;
use WP_Ultimo\Dependencies\Amp\Http\Client\NetworkInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Promise;
use function WP_Ultimo\Dependencies\Amp\call;
class ModifyRequest implements NetworkInterceptor, ApplicationInterceptor
{
    /** @var callable */
    private $mapper;
    public function __construct(callable $mapper)
    {
        $this->mapper = $mapper;
    }
    public final function requestViaNetwork(Request $request, CancellationToken $cancellation, Stream $stream) : Promise
    {
        return call(function () use($request, $cancellation, $stream) {
            $request = (yield call($this->mapper, $request)) ?? $request;
            return $stream->request($request, $cancellation);
        });
    }
    public function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $next) : Promise
    {
        return call(function () use($request, $cancellation, $next) {
            $request = (yield call($this->mapper, $request)) ?? $request;
            return $next->request($request, $cancellation);
        });
    }
}
