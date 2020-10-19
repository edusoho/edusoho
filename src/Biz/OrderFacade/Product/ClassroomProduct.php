<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Classroom\Service\ClassroomService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class ClassroomProduct extends Product implements OrderStatusCallback
{
    const TYPE = 'classroom';

    public $targetType = self::TYPE;

    public $showTemplate = 'order/show/classroom-item.html.twig';

    public function init(array $params)
    {
        $classroom = $this->getClassroomService()->getClassroom($params['targetId']);

        $this->targetId = $params['targetId'];
        $this->backUrl = ['routing' => 'classroom_show', 'params' => ['id' => $classroom['id']]];
        $this->title = $classroom['title'];
        $this->originPrice = $classroom['price'];
        $this->middlePicture = $classroom['middlePicture'];
        $this->maxRate = $classroom['maxRate'];
        $this->productEnable = 'published' === $classroom['status'] ? true : false;
        $this->cover = [
            'small' => $classroom['smallPicture'],
            'middle' => $classroom['middlePicture'],
            'large' => $classroom['largePicture'],
        ];
        $this->successUrl = $this->getSuccessUrl();
    }

    public function validate()
    {
        $access = $this->getClassroomService()->canJoinClassroom($this->targetId);

        $classroom = $this->getClassroomService()->getClassroom($this->targetId);

        if (!$classroom['buyable']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }

        if (AccessorInterface::SUCCESS !== $access['code']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }
    }

    public function onPaid($orderItem)
    {
        $classroomSet = $this->getSettingService()->get('classroom');
        $targetName = empty($classroomSet['name']) ? '班级' : $classroomSet['name'];
        $this->smsCallback($orderItem, $targetName);

        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = [
            'orderId' => $order['id'],
            'note' => $order['created_reason'],
        ];

        try {
            $isStudent = $this->getClassroomService()->isClassroomStudent($orderItem['target_id'], $orderItem['user_id']);
            if (!$isStudent) {
                $member = $this->getClassroomService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
            }

            if (isset($member)) {
                $classroom = $this->getClassroomService()->getClassroom($orderItem['target_id']);
                $this->getLogService()->info('classroom', 'join_classroom', "加入班级《{$classroom['title']}》", ['userId' => $orderItem['user_id'], 'classroomId' => $classroom['id'], 'title' => $classroom['title']]);
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'classroom_callback', 'order.classroom_callback.fail',
                ['error' => $e->getMessage(), 'context' => $orderItem]);

            return false;
        }
    }

    public function onOrderRefundAuditing($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getClassroomService()->lockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getClassroomService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];

        $member = $this->getClassroomService()->getClassroomMember($orderItem['target_id'], $orderItem['user_id']);
        if (!empty($member)) {
            $this->getClassroomService()->removeStudent($orderItem['target_id'], $orderItem['user_id']);
        }

        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getClassroomService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    protected function getSuccessUrl()
    {
        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', [
            'targetType' => $this->targetType,
            'targetId' => $this->targetId,
        ]);

        if (empty($event)) {
            return ['classroom_show', ['id' => $this->targetId]];
        } else {
            return ['information_collect_event', ['eventId' => $event['id']]];
        }
    }

    protected function getMemberOperationService()
    {
        return $this->biz->service('MemberOperation:MemberOperationService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
