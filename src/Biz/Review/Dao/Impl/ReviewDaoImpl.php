<?php

namespace Biz\Review\Dao\Impl;

use Biz\Review\Dao\ReviewDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ReviewDaoImpl extends GeneralDaoImpl implements ReviewDao
{
    protected $table = 'review';

    public function declares()
    {
        return [
            'orderbys' => ['createdTime', 'id'],
            'conditions' => [
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
        ];
    }

    public function getByUserIdAndTargetIdAndTargetType($userId, $targetType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'targetType' => $targetType, 'targetId' => $targetId]);
    }
}
