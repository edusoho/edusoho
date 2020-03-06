<?php

namespace Omnipay\Alipay\Responses;

use Omnipay\Alipay\Requests\AopNotifyRequest;

class AopNotifyResponse extends AbstractResponse
{

    /**
     * @var AopNotifyRequest
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
