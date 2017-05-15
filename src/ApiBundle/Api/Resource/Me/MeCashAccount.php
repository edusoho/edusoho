<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MeCashAccount extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getCashAccountService()->getAccountByUserId($this->getCurrentUser()->getId());
    }

    private function getCashAccountService()
    {
        return $this->service('Cash:CashAccountService');
    }
}