<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Connection;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidCloning;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidSerialization;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Promise;
use WP_Ultimo\Dependencies\Amp\Socket\SocketAddress;
use WP_Ultimo\Dependencies\Amp\Socket\TlsInfo;
final class HttpStream implements Stream
{
    use ForbidSerialization;
    use ForbidCloning;
    /** @var Connection */
    private $connection;
    /** @var callable */
    private $requestCallback;
    /** @var callable|null */
    private $release;
    public function __construct(Connection $connection, callable $requestCallback, callable $releaseCallback)
    {
        $this->connection = $connection;
        $this->requestCallback = $requestCallback;
        $this->release = $releaseCallback;
    }
    public function __destruct()
    {
        if ($this->release !== null) {
            ($this->release)();
        }
    }
    public function request(Request $request, CancellationToken $token) : Promise
    {
        if ($this->release === null) {
            throw new \Error('A stream may only be used for a single request');
        }
        $this->release = null;
        return ($this->requestCallback)(clone $request, $token);
    }
    public function getLocalAddress() : SocketAddress
    {
        return $this->connection->getLocalAddress();
    }
    public function getRemoteAddress() : SocketAddress
    {
        return $this->connection->getRemoteAddress();
    }
    public function getTlsInfo() : ?TlsInfo
    {
        return $this->connection->getTlsInfo();
    }
}
