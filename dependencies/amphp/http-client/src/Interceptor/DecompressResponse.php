<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\ByteStream\ZlibInputStream;
use WP_Ultimo\Dependencies\Amp\CancellationToken;
use WP_Ultimo\Dependencies\Amp\CancellationTokenSource;
use WP_Ultimo\Dependencies\Amp\Http\Client\Connection\Stream;
use WP_Ultimo\Dependencies\Amp\Http\Client\Internal\SizeLimitingInputStream;
use WP_Ultimo\Dependencies\Amp\Http\Client\NetworkInterceptor;
use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
use WP_Ultimo\Dependencies\Amp\Http\Client\Response;
use WP_Ultimo\Dependencies\Amp\Promise;
use function WP_Ultimo\Dependencies\Amp\call;
final class DecompressResponse implements NetworkInterceptor
{
    private $hasZlib;
    public function __construct()
    {
        $this->hasZlib = \extension_loaded('zlib');
    }
    public function requestViaNetwork(Request $request, CancellationToken $cancellation, Stream $stream) : Promise
    {
        return call(function () use($request, $cancellation, $stream) {
            $decodeResponse = \false;
            // If a header is manually set, we won't interfere
            if (!$request->hasHeader('accept-encoding')) {
                $this->addAcceptEncodingHeader($request);
                $decodeResponse = \true;
            }
            if ($onPush = $request->getPushCallable()) {
                $request->onPush(function (Request $request, Promise $promise, CancellationTokenSource $source) use($onPush) {
                    if (!$request->hasHeader('accept-encoding')) {
                        return $onPush($request, $promise, $source);
                    }
                    $promise = call(function () use($promise, $request) {
                        /** @var Response $response */
                        $response = (yield $promise);
                        if ($encoding = $this->determineCompressionEncoding($response)) {
                            /** @noinspection PhpUnhandledExceptionInspection */
                            $response->setBody(new SizeLimitingInputStream(new ZlibInputStream($response->getBody(), $encoding), $request->getBodySizeLimit()));
                            $response->removeHeader('content-encoding');
                        }
                        return $response;
                    });
                    return $onPush($request, $promise, $source);
                });
            }
            /** @var Response $response */
            $response = (yield $stream->request($request, $cancellation));
            if ($decodeResponse && ($encoding = $this->determineCompressionEncoding($response))) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $response->setBody(new SizeLimitingInputStream(new ZlibInputStream($response->getBody(), $encoding), $request->getBodySizeLimit()));
                $response->removeHeader('content-encoding');
            }
            return $response;
        });
    }
    private function addAcceptEncodingHeader(Request $request) : void
    {
        if ($this->hasZlib) {
            $request->setHeader('Accept-Encoding', 'gzip, deflate, identity');
        }
    }
    private function determineCompressionEncoding(Response $response) : int
    {
        if (!$this->hasZlib) {
            return 0;
        }
        if (!$response->hasHeader("content-encoding")) {
            return 0;
        }
        $contentEncodingHeader = \trim($response->getHeader("content-encoding"));
        if (\strcasecmp($contentEncodingHeader, 'gzip') === 0) {
            return \ZLIB_ENCODING_GZIP;
        }
        if (\strcasecmp($contentEncodingHeader, 'deflate') === 0) {
            return \ZLIB_ENCODING_DEFLATE;
        }
        return 0;
    }
}
