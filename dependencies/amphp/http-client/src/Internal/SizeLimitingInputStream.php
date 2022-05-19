<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Internal;

use WP_Ultimo\Dependencies\Amp\ByteStream\InputStream;
use WP_Ultimo\Dependencies\Amp\Failure;
use WP_Ultimo\Dependencies\Amp\Http\Client\ParseException;
use WP_Ultimo\Dependencies\Amp\Http\Status;
use WP_Ultimo\Dependencies\Amp\Promise;
/** @internal */
final class SizeLimitingInputStream implements InputStream
{
    use ForbidSerialization;
    use ForbidCloning;
    /** @var InputStream */
    private $source;
    private $bytesRead = 0;
    private $sizeLimit;
    private $exception;
    public function __construct(InputStream $source, int $sizeLimit)
    {
        $this->source = $source;
        $this->sizeLimit = $sizeLimit;
    }
    public function read() : Promise
    {
        if ($this->exception) {
            return new Failure($this->exception);
        }
        $promise = $this->source->read();
        $promise->onResolve(function ($error, $value) {
            if ($error === null) {
                if ($value !== null) {
                    $this->bytesRead += \strlen($value);
                    if ($this->bytesRead > $this->sizeLimit) {
                        $this->exception = new ParseException("Configured body size exceeded: {$this->bytesRead} bytes received, while the configured limit is {$this->sizeLimit} bytes", Status::PAYLOAD_TOO_LARGE);
                        $this->source = null;
                    }
                }
            }
        });
        return $promise;
    }
}
