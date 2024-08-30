<?php

namespace Biz\Contract\Dao\Impl;

use Biz\Contract\Dao\ContractSignRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContractSignRecordDaoImpl extends GeneralDaoImpl implements ContractSignRecordDao
{
    protected $table = 'contract_sign_record';

    public function getByUserIdAndGoodsKey($userId, $goodsKey)
    {
        return $this->getByFields(['userId' => $userId, 'goodsKey' => $goodsKey]);
    }

    public function declares()
    {
        return [
            'conditions' => [
                'userId = :userId',
                'userId in (:userIds)',
                'goodsKey pre_like :goodsType',
                'goodsKey in (:goodsKeys)',
                'createdTime >= :createdTime_GTE',
                'createdTime <= :createdTime_LTE',
            ],
            'serializes' => [
                'snapshot' => 'json',
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
            ],
        ];
    }
}
