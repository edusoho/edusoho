<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\LegacyNotifyRequest;
class LegacyNotifyResponse extends \Omnipay\Alipay\Responses\AbstractLegacyResponse
{
    /**
     * @var LegacyNotifyRequest
     */
    protected $request;
    public function getResponseText()
    {
        if ($this->isSuccessful()) {
            return 'success';
        } else {
            return 'fail';
        }
    }
    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return true;
    }
}