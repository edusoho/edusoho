<?php

namespace Biz\AntiFraudRemind\Dao\Impl;

use Biz\AntiFraudRemind\Dao\AntiFraudRemindDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AntiFraudRemindDaoImpl extends GeneralDaoImpl implements AntiFraudRemindDao
{
    protected $table = 'anti_fraud_remind';

    public function getByUserId($userId)
    {
        return $this->getByFields([
            'userId' => $userId,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'orderbys' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id IN (:ids)',
                'userId = :userId',
                'id = :id',
            ],
        ];
    }
}
