<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassLiveGroupDaoImpl extends AdvancedDaoImpl implements MultiClassLiveGroupDao
{
    protected $table = 'multi_class_live_group';

    public function getByGroupIdAndLiveId($groupId, $liveId)
    {
        return $this->getByFields(['live_id' => $liveId, 'group_id' => $groupId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
