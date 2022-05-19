<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\ApplicationInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\UnprocessedRequestException;
use WP_Ultimo\Dependencies\Amp\Http\Client\DelegateHttpClient;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Http\Client\SocketException;
use WP_Ultimo\Dependencies\Amp\Promise;
use function WP_Ultimo\Dependencies\Amp\call;
final class RetryRequests implements ApplicationInterceptor
{
    /** @var int */
    private $retryLimit;
    public function __construct(int $retryLimit)
    {
        $this->retryLimit = $retryLimit;
    }
    public function request(Request $request, CancellationToken $cancellation, DelegateHttpClient $next) : Promise
    {
        return call(function () use($request, $cancellation, $next) {
            $attempt = 1;
            do {
                try {
                    return (yield $next->request($request, $cancellation));
                } catch (UnprocessedRequestException $exception) {
                    // Request was deemed retryable by connection, so carry on.
                } catch (SocketException $exception) {
                    if (!$request->isIdempotent()) {
                        throw $exception;
                    }
                    // Request can safely be retried.
                }
            } while ($attempt++ <= $this->retryLimit);
            throw $exception;
        });
    }
}
