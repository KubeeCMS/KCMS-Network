<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Connection;

final class Http2StreamException extends Http2Exception
{
    /** @var int */
    private $streamId;
    public function __construct(string $message, int $streamId, int $code, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->streamId = $streamId;
    }
    public function getStreamId() : int
    {
        return $this->streamId;
    }
}
