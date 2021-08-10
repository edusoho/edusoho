<?php

namespace Biz\TeacherQualification\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Content\Service\FileService;
use Biz\TeacherQualification\Dao\TeacherQualificationDao;
use Biz\TeacherQualification\Service\TeacherQualificationService;
use Biz\User\Dao\UserProfileDao;

class TeacherQualificationServiceImpl extends BaseService implements TeacherQualificationService
{
    public function getByUserId($userId)
    {
        return $this->getTeacherQualificationDao()->getByUserId($userId);
    }

    public function findByUserIds($userIds)
    {
        $qualification = $this->getTeacherQualificationDao()->findByUserIds($userIds);

        return  ArrayToolkit::index($qualification, 'user_id');
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        return $this->getTeacherQualificationDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function count($conditions)
    {
        return $this->getTeacherQualificationDao()->count($conditions);
    }

    public function countTeacherQualification($conditions)
    {
        return $this->getTeacherQualificationDao()->countTeacherQualification($conditions);
    }

    public function searchTeacherQualification($conditions, $orderBys, $start, $limit)
    {
        return $this->getTeacherQualificationDao()->searchTeacherQualification($conditions, $orderBys, $start, $limit);
    }

    /**
     * @throws \Exception
     */
    public function changeQualification($userId, $fields)
    {
        $file = $this->getFileService()->getFile($fields['avatarFileId']);
        $qualification = $this->getByUserId($userId);

        return $this->change($userId, $fields, $file, $qualification);
    }

    protected function change($userId, $fields, $file, $qualification)
    {
        $this->beginTransaction();
        try {
            if (empty($qualification)) {
                $qualificationFields = [
                    'user_id' => $userId,
                    'avatar' => $file['uri'],
                    'code' => $fields['code'],
                ];
                $qualification = $this->getTeacherQualificationDao()->create($qualificationFields);
            } else {
                $qualificationFields = [
                    'avatar' => $file['uri'],
                    'code' => $fields['code'],
                ];
                $qualification = $this->getTeacherQualificationDao()->update($qualification['id'], $qualificationFields);
            }
            if (!empty($fields['truename'])) {
                $this->getProfileDao()->update($qualification['user_id'], ['truename' => trim($fields['truename'])]);
            }

            $profile = $this->getProfileDao()->get($qualification['user_id']);
            $qualification['truename'] = $profile['truename'] ?: '';

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $qualification;
    }

    /**
     * @return TeacherQualificationDao
     */
    protected function getTeacherQualificationDao()
    {
        return $this->createDao('TeacherQualification:TeacherQualificationDao');
    }

    /**
     * @return UserProfileDao
     */
    protected function getProfileDao()
    {
        return $this->createDao('User:UserProfileDao');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
