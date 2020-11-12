<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserActivityLearnFlowDaoImpl extends AdvancedDaoImpl implements UserActivityLearnFlowDao
{
    protected $table = 'user_activity_learn_flow';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }

    public function getBySign($sign)
    {
        return $this->getByFields(['sign' => $sign]);
    }
}
