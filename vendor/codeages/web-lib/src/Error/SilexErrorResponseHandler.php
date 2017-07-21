<?php

namespace Codeages\Weblib\Error;

use Symfony\Component\HttpFoundation\JsonResponse;

class SilexErrorResponseHandler extends AbstractErrorResponseHandler
{
    public function handle(\Exception $e)
    {
        $error = $this->getError($e);

        return new JsonResponse(['error' => $error['error']], $error['http_code']);
    }
}