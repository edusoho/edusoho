<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\MathToolkit;
use Codeages\Biz\Framework\Pay\Service\AccountService;

class MeCashAccount extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $balanceAccount = $this->getAccountService()->getUserBalanceByUserId($this->getCurrentUser()->getId());

        return array(
            'userId' => $balanceAccount['user_id'],
            'cash' => strval(MathToolkit::simple($balanceAccount['amount'], 0.01)),
        );
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->service('Pay:AccountService');
    }
}
