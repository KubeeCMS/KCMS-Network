<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Body;

use WP_Ultimo\Dependencies\Amp\ByteStream\InMemoryStream;
use WP_Ultimo\Dependencies\Amp\ByteStream\InputStream;
use WP_Ultimo\Dependencies\Amp\Http\Client\RequestBody;
use WP_Ultimo\Dependencies\Amp\Promise;
use WP_Ultimo\Dependencies\Amp\Success;
final class StringBody implements RequestBody
{
    private $body;
    public function __construct(string $body)
    {
        $this->body = $body;
    }
    public function createBodyStream() : InputStream
    {
        return new InMemoryStream($this->body !== '' ? $this->body : null);
    }
    public function getHeaders() : Promise
    {
        return new Success([]);
    }
    public function getBodyLength() : Promise
    {
        return new Success(\strlen($this->body));
    }
}
