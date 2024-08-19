<?php

namespace ApiBundle\Api\Resource\Contract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Contract\Service\ContractService;

class ContractPreview extends AbstractResource
{
    use ContractDisplayTrait;

    public function get(ApiRequest $request, $id, $goodsKey)
    {
        $contract = $this->getContractService()->getContract($id);
        $contract['seal'] = AssetHelper::getFurl($contract['seal']);
        $contract['code'] = $this->getContractService()->generateContractCode();
        $contract['signDate'] = date('Y年m月d日');
        $contract['goodsName'] = $this->getGoodsName($goodsKey);

        return $contract;
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }
}
