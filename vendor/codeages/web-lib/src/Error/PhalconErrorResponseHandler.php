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
        $response->setContentType('application/json', 'UTF-8');
        $response->setContent(json_encode(array('error' => $error['error'])));

        return $response;
    }
}
