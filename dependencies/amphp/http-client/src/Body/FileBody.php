<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Body;

use WP_Ultimo\Dependencies\Amp\ByteStream\InputStream;
use WP_Ultimo\Dependencies\Amp\File\Driver;
use WP_Ultimo\Dependencies\Amp\Http\Client\RequestBody;
use WP_Ultimo\Dependencies\Amp\Promise;
use WP_Ultimo\Dependencies\Amp\Success;
use function WP_Ultimo\Dependencies\Amp\call;
use function WP_Ultimo\Dependencies\Amp\File\open;
use function WP_Ultimo\Dependencies\Amp\File\size;
final class FileBody implements RequestBody
{
    /** @var string */
    private $path;
    /**
     * @param string $path The filesystem path for the file we wish to send
     */
    public function __construct(string $path)
    {
        if (!\interface_exists(Driver::class)) {
            throw new \Error("File request bodies require amphp/file to be installed");
        }
        $this->path = $path;
    }
    public function createBodyStream() : InputStream
    {
        $handlePromise = open($this->path, "r");
        return new class($handlePromise) implements InputStream
        {
            /** @var Promise */
            private $promise;
            /** @var InputStream */
            private $stream;
            public function __construct(Promise $promise)
            {
                $this->promise = $promise;
                $this->promise->onResolve(function ($error, $stream) {
                    if ($error) {
                        return;
                    }
                    $this->stream = $stream;
                });
            }
            public function read() : Promise
            {
                if (!$this->stream) {
                    return call(function () {
                        /** @var InputStream $stream */
                        $stream = (yield $this->promise);
                        return $stream->read();
                    });
                }
                return $this->stream->read();
            }
        };
    }
    public function getHeaders() : Promise
    {
        return new Success([]);
    }
    public function getBodyLength() : Promise
    {
        return size($this->path);
    }
}
