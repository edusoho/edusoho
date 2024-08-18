<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Contract\Service\ContractService;

class Contract extends AbstractResource
{
    public function search(ApiRequest $request)
    {
    }

    public function post(ApiRequest $request)
    {
        //鉴权
        $this->getContractService()->createContract($request->request->all());

        return ['ok' => true];
    }

    public function get(ApiRequest $request, $id)
    {
        return $this->getContractService()->getContract($id);
    }

    public function update(ApiRequest $request, $id)
    {
        $this->getContractService()->updateContract($id, $request->request->all());

        return ['ok' => true];
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }
}
