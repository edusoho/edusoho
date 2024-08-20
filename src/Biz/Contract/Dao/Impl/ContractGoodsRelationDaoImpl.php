<?php

namespace Biz\Contract\Dao\Impl;

use Biz\Contract\Dao\ContractGoodsRelationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContractGoodsRelationDaoImpl extends GeneralDaoImpl implements ContractGoodsRelationDao
{
    protected $table = 'contract_goods_relation';

    public function getByGoodsKey($goodsKey)
    {
        return $this->getByFields(['goodsKey' => $goodsKey]);
    }

    public function findByContractIds($contractIds)
    {
        return $this->findInField('contractId', $contractIds);
    }

    public function getByContractId($contractId)
    {
        return $this->getByFields(['contractId' => $contractId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'createdTime',
            ],
        ];
    }
}
