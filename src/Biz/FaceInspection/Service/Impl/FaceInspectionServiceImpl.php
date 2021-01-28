<?php

namespace Biz\FaceInspection\Service\Impl;

/*
 * EduSoho系统可引用以下BaseService
 * Biz\BaseService
 */

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\FaceInspection\Dao\UserFaceDao;
use Biz\FaceInspection\Service\FaceInspectionService;
use Biz\User\Dao\UserDao;

class FaceInspectionServiceImpl extends BaseService implements FaceInspectionService
{
    public function createUserFace($fields)
    {
        $fields = ArrayToolkit::parts($fields, ['picture', 'capture_code', 'user_id']);

        return $this->getUserFaceDao()->create($fields);
    }

    public function updateUserFace($id, $fields)
    {
        return $this->getUserFaceDao()->update($id, $fields);
    }

    public function getUserFaceByUserId($userId)
    {
        return $this->getUserFaceDao()->getByUserId($userId);
    }

    public function countUserFaces($conditions)
    {
        return $this->getUserFaceDao()->count($conditions);
    }

    public function searchUserFaces($conditions, $orderBys, $start, $limit)
    {
        return $this->getUserFaceDao()->search($conditions, $orderBys, $start, $limit);
    }

    /**
     * user join userFace
     */
    public function searchUsersJoinUserFace($conditions, $start, $limit)
    {
        return $this->getUserDao()->searchUsersJoinUserFace($conditions, $start, $limit);
    }

    public function countUsersJoinUserFace($conditions)
    {
        return $this->getUserDao()->countUsersJoinUserFace($conditions);
    }

    /**
     * @return UserFaceDao
     */
    protected function getUserFaceDao()
    {
        return $this->createDao('FaceInspection:UserFaceDao');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('user:UserDao');
    }
}
