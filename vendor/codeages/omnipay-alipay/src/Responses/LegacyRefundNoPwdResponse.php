<?php

namespace Omnipay\Alipay\Responses;

class LegacyRefundNoPwdResponse extends AbstractLegacyResponse
{

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->data['is_success'] === 'T';
    }
}
