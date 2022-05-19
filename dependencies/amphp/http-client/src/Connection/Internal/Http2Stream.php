<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Internal;

use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\Deferred;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidCloning;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\ForbidSerialization;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Struct;
/**
 * Used in Http2Connection.
 *
 * @internal
 */
final class Http2Stream
{
    use Struct;
    use ForbidSerialization;
    use ForbidCloning;
    public const OPEN = 0;
    public const RESERVED = 0b1;
    public const REMOTE_CLOSED = 0b10;
    public const LOCAL_CLOSED = 0b100;
    public const CLOSED = 0b110;
    /** @var Request|null */
    public $request;
    /** @var CancellationToken */
    public $cancellationToken;
    /** @var self|null */
    public $parent;
    /** @var string|null Packed header string. */
    public $headers;
    /** @var int Max header length. */
    public $maxHeaderSize;
    /** @var int Max body length. */
    public $maxBodySize;
    /** @var int Bytes received on the stream. */
    public $received = 0;
    /** @var int */
    public $serverWindow;
    /** @var int */
    public $clientWindow;
    /** @var string */
    public $buffer = "";
    /** @var int */
    public $state;
    /** @var Deferred|null */
    public $deferred;
    /** @var int Integer between 1 and 256 */
    public $weight = 0;
    /** @var int */
    public $dependency = 0;
    /** @var int|null */
    public $expectedLength;
    public function __construct(int $serverSize, int $clientSize, int $maxHeaderSize, int $maxBodySize, int $state = self::OPEN)
    {
        $this->serverWindow = $serverSize;
        $this->maxHeaderSize = $maxHeaderSize;
        $this->maxBodySize = $maxBodySize;
        $this->clientWindow = $clientSize;
        $this->state = $state;
    }
}
