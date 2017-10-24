<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MeCashAccount extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        return $this->getAccountService()->getUserBalanceByUserId($this->getCurrentUser()->getId());
    }

    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}