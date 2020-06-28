<?php

namespace Biz\Favorite\Dao\Impl;

use Biz\Favorite\Dao\FavoriteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements FavoriteDao
{
    protected $table = 'favorite';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => [
                'createdTime', 'id',
            ],
            'conditions' => [
                'userId = :userId',
                'targetType = :targetType',
                'targetId = :targetId',
            ],
        ];
    }

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'targetType' => $targetType, 'targetId' => $targetId]);
    }
}
