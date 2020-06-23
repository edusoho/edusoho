<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Order\Service\OrderService;

class ExerciseMemberServiceImpl extends BaseService implements ExerciseMemberService
{
    public function count($conditions)
    {
        return $this->getExerciseMemberDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit, $columns = [])
    {
        return $this->getExerciseMemberDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function becomeStudentAndCreateOrder($userId, $exerciseId, $data)
    {
        if (!ArrayToolkit::requireds($data, ['price', 'remark'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE);
        }

        if ($this->isExerciseMember($exercise['id'], $user['id'])) {
            $this->createNewException(ItemBankExerciseMemberException::DUPLICATE_MEMBER());
        }

        try {
            $this->beginTransaction();
            if ($data['price'] > 0) {
                $order = $this->createOrder($exercise['id'], $user['id'], $data);
            } else {
                $order = ['id' => 0];
                $info = [
                    'orderId' => $order['id'],
                    'remark' => $data['remark'],
                    'reason' => 'site.join_by_import',
                    'reasonType' => 'import_join',
                ];
                $this->becomeStudent($exercise['id'], $user['id'], $info);
            }

            $member = $this->getExerciseMember($exercise['id'], $user['id']);

            $currentUser = $this->getCurrentUser();
            if (isset($data['isAdminAdded']) && 1 == $data['isAdminAdded']) {
                $message = [
                    'exercise' => $exercise['id'],
                    'exerciseTitle' => $exercise['title'],
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create',
                ];
                $this->getNotificationService()->notify($member['userId'], 'student-create', $message);
            }

            $infoData = [
                'exercise' => $exercise['id'],
                'title' => $exercise['title'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $data['remark'],
            ];

            $this->getLogService()->info(
                'course',
                'add_student',
                "《{$exercise['title']}》(#{$exercise['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}",
                $infoData
            );
            $this->commit();

            return [$exercise, $member, $order];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function isExerciseMember($exerciseId, $userId)
    {
        $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);

        return empty($member) ? false : true;
    }

    public function becomeStudent($exerciseId, $userId, $info = [])
    {
        $exercise = $this->getExerciseService()->get($exerciseId);

        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        if (!in_array($exercise['status'], ['published'])) {
            $this->createNewException(ItemBankExerciseException::UNPUBLISHED_EXERCISE());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $member = $this->getExerciseMemberDao()->getByExerciseIdAndUserId($exerciseId, $userId);

        if ($member) {
            if ('teacher' == $member['role']) {
                return $member;
            } else {
                $this->createNewException(ItemBankExerciseMemberException::DUPLICATE_MEMBER());
            }
        }

        $deadline = 0;
        if ('days' == $exercise['expiryMode'] && $exercise['expiryDays'] > 0) {
            $endTime = strtotime(date('Y-m-d', time()).' 23:59:59'); //系统当前时间
            $deadline = $exercise['expiryDays'] * 24 * 60 * 60 + $endTime;
        } elseif ('date' == $exercise['expiryMode'] || 'end_date' == $exercise['expiryMode']) {
            $deadline = $exercise['expiryEndDate'];
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                $this->createNewException(OrderException::NOTFOUND_ORDER());
            }
        } else {
            $order = null;
        }

        $fields = [
            'exerciseId' => $exerciseId,
            'questionBankId' => $exercise['questionBankId'],
            'userId' => $userId,
            'orderId' => empty($order) ? 0 : $order['id'],
            'deadline' => $deadline,
            'role' => 'student',
            'remark' => empty($info['remark']) ? '' : $info['remark'],
            'createdTime' => time(),
        ];

        $reason = $this->buildJoinReason($info, $order);
        $member = $this->addMember($fields, $reason);

        $this->dispatchEvent(
            'exercise.join',
            $exercise,
            ['userId' => $member['userId'], 'member' => $member]
        );

        return $member;
    }

    public function addTeacher($exerciseId)
    {
        $exercise = $this->getExerciseDao()->get($exerciseId);
        $userId = $this->getCurrentUser()->getId();
        $teacher = [
            'exerciseId' => $exerciseId,
            'questionBankId' => $exercise['questionBankId'],
            'userId' => $userId,
            'role' => 'teacher',
            'remark' => ''
        ];
        $member = $this->addMember($teacher);
        $fields = ['teacherIds' => [$userId]];
        $this->getExerciseDao()->update($exerciseId, $fields);
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

    private function buildJoinReason($info, $order)
    {
        if (ArrayToolkit::requireds($info, ['reason', 'reasonType'])) {
            return ArrayToolkit::parts($info, ['reason', 'reasonType']);
        }

        $orderId = empty($order) ? 0 : $order['id'];

        return $this->getMemberOperationRecordService()->getJoinReasonByOrderId($orderId);
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
