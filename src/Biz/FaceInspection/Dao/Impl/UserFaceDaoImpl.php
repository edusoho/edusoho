<?php

namespace Biz\FaceInspection\Dao\Impl;

use Biz\FaceInspection\Dao\UserFaceDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserFaceDaoImpl extends GeneralDaoImpl implements UserFaceDao
{
    protected $table = 'user_face';

    public function getByUserId($userId)
    {
        return $this->getByFields(['user_id' => $userId]);
    }

    public function declares()
    {
        return [
            'orderbys' => [
                'id',
                'updated_time',
                'created_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'id IN ( :ids )',
                'user_id IN ( :user_ids )',
                'user_id = :user_id',
            ],
        ];
    }
}
