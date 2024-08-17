<?php

namespace Biz\Contract\Service\Impl;

use Biz\BaseService;
use Biz\Contract\Dao\ContractDao;
use Biz\Contract\Dao\ContractGoodsRelationDao;
use Biz\Contract\Dao\ContractSignRecordDao;
use Biz\Contract\Service\ContractService;

class ContractServiceImpl extends BaseService implements ContractService
{
    public function createContract(array $params)
    {
        // TODO: Implement createContract() method.
    }

    public function getContract($id)
    {
        return $this->getContractDao()->get($id);
    }

    public function signContract($id, $sign)
    {
        // TODO: Implement signContract() method.
    }

    public function getBindContractByGoodsTypeAndTargetId($goodsType, $targetId)
    {
        $relation = $this->getContractGoodsRelationDao()->getByGoodsTypeAndTargetId($goodsType, $targetId);
        if (empty($relation)) {
            return null;
        }
        $contract = $this->getContract($relation['contractId']);
        $relation['name'] = $contract['name'];

        return $relation;
    }

    public function getSignRecordByUserIdAndGoodsTypeAndTargetId($userId, $goodsType, $targetId)
    {
        return $this->getContractSignRecordDao()->getByUserIdAndGoodsTypeAndTargetId($userId, $goodsType, $targetId);
    }

    /**
     * @return ContractDao
     */
    private function getContractDao()
    {
        return $this->createDao('Contract:ContractDao');
    }

    /**
     * @return ContractGoodsRelationDao
     */
    private function getContractGoodsRelationDao()
    {
        return $this->createDao('Contract:ContractGoodsRelationDao');
    }

    /**
     * @return ContractSignRecordDao
     */
    private function getContractSignRecordDao()
    {
        return $this->createDao('Contract:ContractSignRecordDao');
    }
}
