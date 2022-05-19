<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
final class SetRequestHeaderIfUnset extends ModifyRequest
{
    public function __construct(string $headerName, string $headerValue, string ...$headerValues)
    {
        \array_unshift($headerValues, $headerValue);
        parent::__construct(static function (Request $request) use($headerName, $headerValues) {
            if (!$request->hasHeader($headerName)) {
                $request->setHeader($headerName, $headerValues);
            }
            return $request;
        });
    }
}
