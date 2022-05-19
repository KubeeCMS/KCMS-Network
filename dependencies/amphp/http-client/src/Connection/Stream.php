<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Connection;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\DelegateHttpClient;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Promise;
use WP_Ultimo\Dependencies\Amp\Socket\SocketAddress;
use WP_Ultimo\Dependencies\Amp\Socket\TlsInfo;
interface Stream extends DelegateHttpClient
{
    /**
     * @param Request           $request
     * @param CancellationToken $token
     *
     * @return Promise
     *
     * @throws \Error Thrown if this method is called more than once.
     */
    public function request(Request $request, CancellationToken $token) : Promise;
    public function getLocalAddress() : SocketAddress;
    public function getRemoteAddress() : SocketAddress;
    public function getTlsInfo() : ?TlsInfo;
}
