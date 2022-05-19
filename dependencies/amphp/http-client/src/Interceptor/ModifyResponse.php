<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\CancellationTokenSource;
use WP_Ultimo\Dependencies\Amp\Http\Client\ApplicationInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Http\Client\DelegateHttpClient;
use WP_Ultimo\Dependencies\Amp\Http\Client\NetworkInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Http\Client\Response;
use WP_Ultimo\Dependencies\Amp\Promise;
use function WP_Ultimo\Dependencies\Amp\call;
class ModifyResponse implements NetworkInterceptor, ApplicationInterceptor
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
            /** @var Response $response */
            $response = (yield $stream->request($request, $cancellation));
            return (yield call($this->mapper, $response)) ?? $response;
        });
    }
    public function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $next) : Promise
    {
        return call(function () use($request, $cancellation, $next) {
            if ($onPush = $request->getPushCallable()) {
                $request->onPush(function (Request $request, Promise $promise, CancellationTokenSource $source) use($onPush) {
                    $promise = call(function () use($promise) {
                        $response = (yield $promise);
                        return (yield call($this->mapper, $response)) ?? $response;
                    });
                    return $onPush($request, $promise, $source);
                });
            }
            /** @var Response $response */
            $response = (yield $next->request($request, $cancellation));
            return (yield call($this->mapper, $response)) ?? $response;
        });
    }
}
