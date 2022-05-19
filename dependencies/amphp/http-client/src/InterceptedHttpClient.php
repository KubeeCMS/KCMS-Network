<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidCloning;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidSerialization;
use WP_Ultimo\Dependencies\Amp\NullCancellationToken;
use WP_Ultimo\Dependencies\Amp\Promise;
final class InterceptedHttpClient implements HttpClient
{
    use ForbidCloning;
    use ForbidSerialization;
    /** @var HttpClient */
    private $httpClient;
    /** @var ApplicationInterceptor */
    private $interceptor;
    public function __construct(HttpClient $httpClient, ApplicationInterceptor $interceptor)
    {
        $this->httpClient = $httpClient;
        $this->interceptor = $interceptor;
    }
    public function request(Request $request, ?CancellationToken $cancellation = null) : Promise
    {
        $cancellation = $cancellation ?? new NullCancellationToken();
        return $this->interceptor->request(clone $request, $cancellation, $this->httpClient);
    }
}
