<?php

namespace Biz\ItemBankExercise\Member;

use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Biz\OrderFacade\Product\ItemBankExerciseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;

class StudentMember extends Member
{
    const ROLE = 'student';

    protected function addMember($exercise, $userId, $info)
    {
        if (!empty($info['price']) && $info['price'] > 0) {
            $this->createOrder($exercise['id'], $userId, $info);

            return $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $userId);
        }
        $exercise['expiryMode'] = $info['expiryMode'] ?? $exercise['expiryMode'];
        $exercise['expiryDays'] = $info['expiryDays'] ?? $exercise['expiryDays'];

        $member = [
            'exerciseId' => $exercise['id'],
            'questionBankId' => $exercise['questionBankId'],
            'userId' => $userId,
            'deadline' => ExpiryModeFactory::create($exercise['expiryMode'])->getDeadline($exercise),
            'role' => self::ROLE,
            'remark' => $info['remark'],
            'orderId' => empty($info['orderId']) ? 0 : $info['orderId'],
        ];

        return $this->getMemberDao()->create($member);
    }

    protected function beforeAdd($exerciseId, $userId, $info)
    {
        $exercise = $this->canJoin($exerciseId, $userId);
        $this->validate($exercise, $info);

        return $exercise;
    }

    protected function afterAdd($member, $exercise, $info)
    {
        if ($member['orderId'] > 0) {
            $order = $this->getOrderService()->getOrder($member['orderId']);
            if ('outside' == $order['source'] && !empty($info['reason']) && OperateReason::JOIN_BY_PURCHASE == $info['reason']) {
                return;
            }
        }
        $this->recordOperation($member, $info);

        $this->recordLog($member, $exercise, $info);
    }

    protected function recordOperation($member, $info)
    {
        $currentUser = $this->biz['user'];
        $exercise = $this->getExerciseService()->get($member['exerciseId']);

        if (empty($info['reason']) || empty($info['reasonType'])) {
           throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $record = [
            'userId' => $member['userId'],
            'memberId' => $member['id'],
            'memberType' => $member['role'],
            'exerciseId' => $member['exerciseId'],
            'operateType' => 'join',
            'operateTime' => time(),
            'operatorId' => $currentUser['id'],
            'orderId' => $member['orderId'],
            'title' => $exercise['title'],
            'reason' => empty($info['reason']) ? '' : $info['reason'],
            'reasonType' => empty($info['reasonType']) ? '' : $info['reasonType'],
        ];

        return $this->getMemberOperationRecordService()->create($record);
    }

    protected function recordLog($member, $exercise, $info)
    {
        $user = $this->getUserService()->getUser($member['userId']);
        $this->getLogService()->info(
            'item_bank_exercise',
            'add_student',
            "《{$exercise['title']}》(#{$exercise['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$info['remark']}",
            [
                'exercise' => $exercise['id'],
                'title' => $exercise['title'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $info['remark'],
            ]
        );
    }

    protected function canJoin($exerciseId, $userId)
    {
        $exercise = $this->getExerciseService()->get($exerciseId);

        if (empty($exercise)) {
            throw ItemBankExerciseException::NOTFOUND_EXERCISE();
        }

        if (!in_array($exercise['status'], ['published'])) {
            throw ItemBankExerciseException::UNPUBLISHED_EXERCISE();
        }

        if ($this->getExerciseMemberService()->isExerciseMember($exerciseId, $userId)) {
            throw ItemBankExerciseMemberException::DUPLICATE_MEMBER();
        }

        $expiryMode = ExpiryModeFactory::create($exercise['expiryMode']);
        if ($expiryMode->isExpired($exercise)) {
            throw ItemBankExerciseMemberException::CAN_NOT_BECOME_MEMBER();
        }

        return $exercise;
    }

    public function validate($exercise, $data)
    {
        if (!isset($data['remark'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (!empty($data['price']) && !ArrayToolkit::requireds($data, ['source'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
    }

    protected function createOrder($exerciseId, $userId, $data)
    {
        $courseProduct = $this->getOrderFacadeService()->getOrderProduct(ItemBankExerciseProduct::TYPE, ['targetId' => $exerciseId]);

        $params = [
            'created_reason' => $data['remark'],
            'source' => $data['source'],
            'create_extra' => $data,
            'deducts' => empty($data['deducts']) ? [] : $data['deducts'],
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($courseProduct, $userId, $params);
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->biz->service('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->biz->service('OrderFacade:OrderFacadeService');
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
