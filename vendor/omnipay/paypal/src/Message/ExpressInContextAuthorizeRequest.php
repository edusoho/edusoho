<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express In-Context Authorize Request
 */
class ExpressInContextAuthorizeRequest extends ExpressAuthorizeRequest
{
    protected function createResponse($data)
    {
        return $this->response = new ExpressInContextAuthorizeResponse($this, $data);
    }
}
