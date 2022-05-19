<?php

namespace WP_Ultimo\Dependencies\Amp\Http\Client\Interceptor;

use WP_Ultimo\Dependencies\Amp\Http\Client\HttpException;
use WP_Ultimo\Dependencies\Amp\Http\Client\Response;
class TooManyRedirectsException extends HttpException
{
    private $response;
    public function __construct(Response $response)
    {
        parent::__construct("There were too many redirects");
        $this->response = $response;
    }
    public function getResponse() : Response
    {
        return $this->response;
    }
}
