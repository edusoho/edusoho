<?php

namespace Omnipay\Alipay\Responses;

class VerifyNotifyIdResponse extends AbstractLegacyResponse
{

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return ($this->data['result'] . '') === 'true';
    }
}
