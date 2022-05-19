<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\Http\Client\Request;
final class RemoveRequestHeader extends ModifyRequest
{
    public function __construct(string $headerName)
    {
        parent::__construct(static function (Request $request) use($headerName) {
            $request->removeHeader($headerName);
            return $request;
        });
    }
}
