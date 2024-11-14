<?php

namespace ApiBundle\Api\Resource\SimpleContract;

use ApiBundle\Api\Resource\AbstractResource;
use Biz\Contract\Service\ContractService;

class SimpleContract extends AbstractResource
{
    public function search()
    {
        return $this->getContractService()->searchContracts([], ['updatedTime' => 'DESC'], 0, PHP_INT_MAX, ['id', 'name']);
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }
}
