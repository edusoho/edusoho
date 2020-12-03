<?php

namespace Omnipay\PayPal\Message;

/**
 * PayPal Express In-Context Authorize Response
 */
class ExpressInContextAuthorizeResponse extends ExpressAuthorizeResponse
{
    protected $liveCheckoutEndpoint = 'https://www.paypal.com/checkoutnow';
    protected $testCheckoutEndpoint = 'https://www.sandbox.paypal.com/checkoutnow';

    protected function getRedirectQueryParameters()
    {
        return array(
            'useraction' => 'commit',
            'token' => $this->getTransactionReference(),
        );
    }
}
