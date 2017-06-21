<?php

namespace Codeages\Weblib\Error;

use Phalcon\Http\Response;

class PhalconErrorResponseHandler extends AbstractErrorResponseHandler
{
    public function handle(\Exception $e)
    {
        $error = $this->getError($e);

        $response = new Response();
        $response->setStatusCode($error['http_code']);
        $response->setContent(json_encode($error['error']));

        return $response;
    }
}
