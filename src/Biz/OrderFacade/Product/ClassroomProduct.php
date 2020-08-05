<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Classroom\Service\ClassroomService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class ClassroomProduct extends BaseGoodsProduct implements OrderStatusCallback
{
    public $goods;

    public $goodsSpecs;

    public $originalTargetId;

    /**
     * 班级展示价格
     *
     * @var float
     */
    public $price;

    const TYPE = 'classroom';

    public $targetType = self::TYPE;

    public $showTemplate = 'order/show/classroom-item.html.twig';

    public function init(array $params)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($params['targetId']);
        $goods = $this->getGoodsService()->getGoods($goodsSpecs['goodsId']);
        $this->targetId = $params['targetId'];
        //originalTargetId 兼容老数据，用来处理老数据的问题：这里originalTargetId对应的是classroom的id
        $this->originalTargetId = $goodsSpecs['targetId'];
        $this->goods = $goods;
        $this->goodsSpecs = $goodsSpecs;

        $this->backUrl = ['routing' => 'goods_show', 'params' => ['id' => $goodsSpecs['goodsId']]];
        $this->title = $goodsSpecs['title'];
        $this->cover = empty($goodsSpecs['images']) ? $goods['images'] : $goodsSpecs['images'];
        $this->successUrl = ['routing' => 'classroom_show', 'params' => ['id' => $goodsSpecs['targetId']]];

        $this->productEnable = 'published' === $goods['status'] && 'published' === $goodsSpecs['status'];
        $this->originPrice = $goodsSpecs['price'];
    }

    public function validate()
    {
        $access = $this->getClassroomService()->canJoinClassroom($this->goodsSpecs['targetId']);

        $classroom = $this->getClassroomService()->getClassroom($this->goodsSpecs['targetId']);

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
            $classroom = $this->getClassroomByGoodsSpecsId($orderItem['target_id']);

            $isStudent = $this->getClassroomService()->isClassroomStudent($classroom['id'], $orderItem['user_id']);
            if (!$isStudent) {
                $member = $this->getClassroomService()->becomeStudent($classroom['id'], $orderItem['user_id'], $info);
            }

            if (isset($member)) {
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
        $classroom = $this->getClassroomByGoodsSpecsId($orderItem['target_id']);

        $this->getClassroomService()->lockStudent($classroom['id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $classroom = $this->getClassroomByGoodsSpecsId($orderItem['target_id']);

        $this->getClassroomService()->unlockStudent($classroom['id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $classroom = $this->getClassroomByGoodsSpecsId($orderItem['target_id']);

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $orderItem['user_id']);
        if (!empty($member)) {
            $this->getClassroomService()->removeStudent($classroom['id'], $orderItem['user_id']);
        }

        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $classroom = $this->getClassroomByGoodsSpecsId($orderItem['target_id']);

        $this->getClassroomService()->unlockStudent($classroom['id'], $orderItem['user_id']);
    }

    protected function getClassroomByGoodsSpecsId($id)
    {
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecs($id);

        return $this->getClassroomService()->getClassroom($goodsSpecs['targetId']);
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
