<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\User\UserException;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{
    public function get($exerciseId)
    {
        return $this->getExerciseDao()->get($exerciseId);
    }

    public function count($conditions)
    {
        return $this->getExerciseDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareCourseConditions($conditions);

        return $this->getExerciseDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function tryManageExercise($exerciseId = 0)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $exercise = $this->getExerciseDao()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        if (!$this->hasCourseManagerRole($exerciseId)) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_MANAGE_EXERCISE());
        }

        return $exercise;
    }

    public function hasCourseManagerRole($exerciseId = 0)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        $exercise = $this->get($exerciseId);
        //课程不存在，无权限管理
        if (empty($exercise)) {
            return false;
        }

        if (in_array($user->getId(), $exercise['teacherIds'])) {
            return true;
        }

        return false;
    }

    public function updateExerciseStatistics($id, $fields)
    {
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $updateFields = [];
        foreach ($fields as $field) {
            if ('studentNum' === $field) {
                $updateFields['studentNum'] = $this->countStudentsByExerciseId($id);
            }
        }

        if (empty($updateFields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $exercise = $this->getExerciseDao()->update($id, $updateFields);

        return $exercise;
    }

    public function countStudentsByExerciseId($exerciseId)
    {
        return $this->getExerciseMemberDao()->count(
            [
                'exerciseId' => $exerciseId,
                'role' => 'student',
            ]
        );
    }

    public function isExerciseTeacher($exerciseId, $userId)
    {
        $member = $this->getExerciseDao()->get($exerciseId);

        return !empty($member) && in_array($userId, $member['teacherIds']);
    }

    protected function hasAdminRole()
    {
        $user = $this->getCurrentUser();

        return $user->hasPermission('admin_course_content_manage') || $user->hasPermission('admin_v2_course_content_manage');
    }

    // todo
    public function canLearningExercise($exerciseId, $userId)
    {
        return true;
    }

    protected function _prepareCourseConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (0 == $value) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }
}
