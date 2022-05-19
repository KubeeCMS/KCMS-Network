<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Connection;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Promise;
interface ConnectionPool
{
    /**
     * @param Request           $request
     * @param CancellationToken $token
     *
     * @return Promise<Stream>
     */
    public function getStream(Request $request, CancellationToken $token) : Promise;
    /**
     * @return string[] Array of supported protocol versions.
     */
    public function getProtocolVersions() : array;
}
