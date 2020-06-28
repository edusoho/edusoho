<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\BaseService;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Codeages\Biz\Order\Service\OrderService;

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

    public function becomeStudent($exerciseId, $userId, $info = [])
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if (!in_array($exercise['status'], ['published'])) {
            $this->createNewException(ItemBankExerciseException::UNPUBLISHED_EXERCISE());
        }

        if ($this->isExerciseMember($exerciseId, $userId)) {
            $this->createNewException(ItemBankExerciseMemberException::DUPLICATE_MEMBER());
        }

        try{
            $this->beginTransaction();
            $deadline = 0;
            if ('days' == $exercise['expiryMode'] && $exercise['expiryDays'] > 0) {
                $endTime = strtotime(date('Y-m-d', time()).' 23:59:59'); //系统当前时间
                $deadline = $exercise['expiryDays'] * 24 * 60 * 60 + $endTime;
            } elseif ('date' == $exercise['expiryMode'] || 'end_date' == $exercise['expiryMode']) {
                $deadline = $exercise['expiryEndDate'];
            }

            $fields = [
                'exerciseId' => $exerciseId,
                'questionBankId' => $exercise['questionBankId'],
                'userId' => $userId,
                'deadline' => $deadline,
                'role' => 'student',
                'remark' => empty($info['remark']) ? '' : $info['remark'],
                'createdTime' => time(),
            ];

            $reason = [
                'reason' => 'site.join_by_import',
                'reasonType' => 'import_join',
            ];
            $member = $this->addMember($fields, $reason);

            $user = $this->getUserService()->getUser($userId);
            $infoData = [
                'exercise' => $exercise['id'],
                'title' => $exercise['title'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $info['remark'],
            ];
            $this->getLogService()->info(
                'course',
                'add_student',
                "《{$exercise['title']}》(#{$exercise['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$info['remark']}",
                $infoData
            );

            $this->dispatchEvent(
                'exercise.join',
                $exercise,
                ['userId' => $member['userId'], 'member' => $member]
            );
            $this->commit();
        }catch (\Exception $e){
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

    public function getByEerciseIdAndUserId($exerciseId, $userId)
    {
        return $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);
    }
    
    public function addTeacher($exerciseId)
    {
        try{
            $this->beginTransaction();
            $exercise = $this->getExerciseService()->tryManageExercise($exerciseId,0);
            $userId = $this->getCurrentUser()->getId();
            $teacher = [
                'exerciseId' => $exerciseId,
                'questionBankId' => $exercise['questionBankId'],
                'userId' => $userId,
                'role' => 'teacher',
                'remark' => '',
            ];
            $member = $this->addMember($teacher);
            $fields = ['teacherIds' => [$userId]];
            $this->getExerciseDao()->update($exerciseId, $fields);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            throw $e;
        }

        return $member;
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

    private function addMember($member, $reason = [])
    {
        try {
            $this->beginTransaction();
            $member = $this->getExerciseMemberDao()->create($member);
            if (!empty($reason)) {
                $this->createOperateRecord($member, 'join', $reason);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $member;
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

    protected function createOrder($exerciseId, $userId, $data)
    {
        $courseProduct = $this->getOrderFacadeService()->getOrderProduct('exercise', ['targetId' => $exerciseId]);

        $params = [
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? [] : $data['deducts'],
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($courseProduct, $userId, $params);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->biz->service('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
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
