<?php

namespace Biz\FaceInspection\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\FaceInspection\Dao\UserFaceDao;

class UserFaceDaoImpl extends GeneralDaoImpl implements UserFaceDao
{
    protected $table = 'user_face';

    public function getByUserId($userId)
    {
        return $this->getByFields(array('user_id' => $userId));
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'id',
                'updated_time',
                'created_time',
            ),
            'timestamps' => array(
                'created_time',
                'updated_time',
            ),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'user_id IN ( :user_ids )',
                'user_id = :user_id',
            ),
        );
    }
}
