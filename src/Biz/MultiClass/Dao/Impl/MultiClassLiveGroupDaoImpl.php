<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassLiveGroupDaoImpl extends AdvancedDaoImpl implements MultiClassLiveGroupDao
{
    protected $table = 'multi_class_live_group';

    public function findByGroupIds($groupIds)
    {
        return $this->findInField('group_id', $groupIds);
    }

    public function getByGroupId($groupId)
    {
        return $this->getByFields(['group_id' => $groupId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
            'orderbys' => ['id', 'created_time'],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
            ],
        ];
    }
}
