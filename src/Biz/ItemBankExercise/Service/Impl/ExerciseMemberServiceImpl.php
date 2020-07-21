<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Member\MemberManage;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;

class ExerciseMemberServiceImpl extends BaseService implements ExerciseMemberService
{
    public function count($conditions)
    {
        return $this->getExerciseMemberDao()->count($conditions);
    }

    public function update($id, $member)
    {
        $member = ArrayToolkit::parts($member, ['doneQuestionNum', 'rightQuestionNum', 'masteryRate', 'completionRate']);

        return $this->getExerciseMemberDao()->update($id, $member);
    }

    public function search($conditions, $orderBy, $start, $limit, $columns = [])
    {
        return $this->getExerciseMemberDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function becomeStudent($exerciseId, $userId, $info)
    {
        try {
            $this->beginTransaction();

            $manage = new MemberManage($this->biz);
            $studentMember = $manage->getMemberClass('student');
            $member = $studentMember->join($exerciseId, $userId, $info);
            $exercise = $this->getExerciseService()->get($exerciseId);

            $this->dispatchEvent('exercise.join', $exercise, ['member' => $member]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $member;
    }

    public function addTeacher($exerciseId)
    {
        try {
            $this->beginTransaction();

            $userId = $this->getCurrentUser()->getId();
            $manage = new MemberManage($this->biz);
            $teacherMember = $manage->getMemberClass('teacher');
            $member = $teacherMember->join($exerciseId, $userId, ['remark' => '']);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $member;
    }

    public function isExerciseMember($exerciseId, $userId)
    {
        $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);

        return empty($member) ? false : true;
    }

    public function getByExerciseIdAndUserId($exerciseId, $userId)
    {
        return $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);
    }

    public function lockStudent($exerciseId, $userId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $this->getExerciseMember($exerciseId, $userId);
        if (empty($member)) {
            return;
        }

        if ('student' != $member['role']) {
            $this->createNewException(ItemBankExerciseMemberException::MEMBER_NOT_STUDENT());
        }

        if ($member['locked']) {
            return;
        }

        return $this->getExerciseMemberDao()->update($member['id'], ['locked' => 1]);
    }

    public function unlockStudent($exerciseId, $userId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $this->getExerciseMember($exerciseId, $userId);
        if (empty($member)) {
            return;
        }

        if ('student' != $member['role']) {
            $this->createNewException(ItemBankExerciseMemberException::MEMBER_NOT_STUDENT());
        }

        if (empty($member['locked'])) {
            return;
        }

        return $this->getExerciseMemberDao()->update($member['id'], ['locked' => 0]);
    }

    public function removeStudent($exerciseId, $userId, $reason = [])
    {
        $exercise = $this->getExerciseService()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $this->getByExerciseIdAndUserId($exerciseId, $userId);

        if (empty($member) || ('student' != $member['role'])) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_MEMBER());
        }

        try {
            $this->beginTransaction();

            $reason = ArrayToolkit::parts($reason, ['reason', 'reason_type']);

            $this->removeMember($member, $reason);

            $this->dispatchEvent('exercise.quit', $exercise, ['member' => $member]);

            $user = $this->getUserService()->getUser($userId);
            $this->getLogService()->info(
                'item_bank_exercise',
                'remove_student',
                "《{$exercise['title']}》(#{$exercise['id']})，移除学员{$user['nickname']}(#{$user['id']})}"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getExerciseMember($exerciseId, $userId)
    {
        return $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);
    }

    public function remarkStudent($exerciseId, $userId, $remark)
    {
        $member = $this->getExerciseMember($exerciseId, $userId);

        if (empty($member)) {
            $this->createNewException(ItemBankExerciseMemberException::NOTFOUND_MEMBER());
        }

        $fields = ['remark' => empty($remark) ? '' : (string) $remark];

        return $this->getExerciseMemberDao()->update($member['id'], $fields);
    }

    public function batchUpdateMemberDeadlines($exerciseId, $userIds, $setting)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        $expiryMode = ExpiryModeFactory::create($exercise['expiryMode']);
        foreach ($userIds as $userId) {
            $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);
            $deadline = $expiryMode->getUpdateDeadline($member, $setting);
            $this->getExerciseMemberDao()->update($member['id'], ['deadline' => $deadline]);
        }
    }

    public function checkUpdateDeadline($exerciseId, $userIds, $setting)
    {
        $members = $this->search(
            ['userIds' => $userIds, 'exerciseId' => $exerciseId],
            ['deadline' => 'ASC'],
            0,
            PHP_INT_MAX
        );
        $member = array_shift($members);

        if (isset($setting['day'])) {
            if ('minus' == $setting['waveType']) {
                $maxAllowMinusDay = intval(($member['deadline'] - time()) / (24 * 3600));
                if ($setting['day'] > $maxAllowMinusDay) {
                    return false;
                }
            }
        } else {
            $deadline = TimeMachine::isTimestamp($setting['deadline']) ? $setting['deadline'] : strtotime($setting['deadline'].' 23:59:59');
            if ($deadline < $member['deadline'] || time() > $deadline) {
                return false;
            }
        }

        return true;
    }

    public function isMemberNonExpired($exercise, $member)
    {
        if (empty($exercise) || empty($member)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (0 == $member['deadline']) {
            return true;
        }

        if ($member['deadline'] > time()) {
            return true;
        } else {
            return false;
        }
    }

    public function quitExerciseByDeadlineReach($userId, $exerciseId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);
        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);
        if (empty($member) || ('student' != $member['role'])) {
            $this->createNewException(ItemBankExerciseMemberException::NOTFOUND_MEMBER());
        }

        $isNonExpired = $this->isMemberNonExpired($exercise, $member);
        if ($isNonExpired) {
            $this->createNewException(ItemBankExerciseMemberException::NON_EXPIRED_MEMBER());
        }

        try {
            $this->beginTransaction();

            $this->removeMember($member, []);

            $user = $this->getUserService()->getUser($userId);
            $this->getLogService()->info(
                'item_bank_exercise',
                'remove_student',
                "《{$exercise['title']}》(#{$exercise['id']})，学员({$user['nickname']})因达到有效期退出教学计划(#{$member['id']})"
            );
            $this->dispatchEvent('exercise.quit', $exercise, ['member' => $member]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findByUserIdAndRole($userId, $role)
    {
        return $this->getExerciseMemberDao()->findByUserIdAndRole($userId, $role);
    }

    private function removeMember($member, $reason = [])
    {
        try {
            $this->beginTransaction();
            $result = $this->getExerciseMemberDao()->delete($member['id']);
            if (!empty($reason)) {
                $this->createOperateRecord($member, 'exit', $reason);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $result;
    }

    protected function createOperateRecord($member, $operateType, $reason)
    {
        $currentUser = $this->getCurrentUser();
        $exercise = $this->getExerciseService()->get($member['exerciseId']);
        $record = [
            'userId' => $member['userId'],
            'memberId' => $member['id'],
            'memberType' => $member['role'],
            'exerciseId' => $member['exerciseId'],
            'operateType' => $operateType,
            'operateTime' => time(),
            'operatorId' => $currentUser['id'],
            'orderId' => $member['orderId'],
            'title' => $exercise['title'],
        ];

        $record = array_merge($record, $reason);
        $record = $this->getMemberOperationRecordService()->create($record);

        return $record;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->biz->service('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }

    /**
     * @return ExerciseDao
     */
    protected function getExerciseDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseDao');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
