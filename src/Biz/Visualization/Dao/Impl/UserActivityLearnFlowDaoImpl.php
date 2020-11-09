<?php

namespace Biz\Visualization\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserActivityLearnFlowDaoImpl extends AdvancedDaoImpl
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
}
