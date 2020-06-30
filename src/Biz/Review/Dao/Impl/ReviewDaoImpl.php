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
            'orderbys' => ['createdTime', 'id', 'updatedTime', 'rating'],
            'conditions' => [
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
                'parentId = :parentId',
                'targetId IN (:targetIds)',
                'userId IN (:userIds)',
                'content LIKE :content',
                'rating = :rating',
                'targetType IN (:targetTypes)',
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

    public function sumRatingByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(rating)');

        return $builder->execute()->fetchColumn(0);
    }

    public function deleteByParentId($parentId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE parentId = ?";

        return $this->db()->executeQuery($sql, [$parentId]);
    }

    public function deleteByTargetTypeAndTargetId($targetType, $targetId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE targetType = ? AND targetId = ?";

        return $this->db()->executeQuery($sql, [$targetType, $targetId]);
    }
}
