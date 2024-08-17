<?php

namespace Biz\Contract\Dao\Impl;

use Biz\Contract\Dao\ContractSignRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ContractSignRecordDaoImpl extends GeneralDaoImpl implements ContractSignRecordDao
{
    protected $table = 'contract_sign_record';

    public function getByUserIdAndGoodsTypeAndTargetId($userId, $goodsType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'goodsType' => $goodsType, 'targetId' => $targetId]);
    }

    public function declares()
    {
        return [
            'conditions' => [
                'userId = :userId',
            ],
            'serializes' => [
                'contractSnapshot' => 'json',
            ],
            'orderbys' => [
                'createdTime',
            ],
            'timestamps' => [
                'createdTime',
            ],
        ];
    }
}
