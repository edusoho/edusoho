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
            'serializes' => ['meta' => 'json'],
            'orderbys' => ['createdTime', 'id'],
            'conditions' => [
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
                'parentId = :parentId',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
        ];
    }

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'targetType' => $targetType, 'targetId' => $targetId]);
    }
}
