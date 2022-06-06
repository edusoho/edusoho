<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\ItemBankExercise\OperateReason;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class ItemBankExerciseProduct extends Product implements OrderStatusCallback
{
    const TYPE = 'item_bank_exercise';

    public $targetType = self::TYPE;

    public $showTemplate = 'order/show/exercise-item.html.twig';

    public function init(array $params)
    {
        $exercise = $this->getExerciseService()->get($params['targetId']);
        $this->targetId = $params['targetId'];
        $this->backUrl = ['routing' => 'item_bank_exercise_show', 'params' => ['id' => $exercise['id']]];
        $this->successUrl = ['item_bank_exercise_show', ['id' => $this->targetId]];
        $this->title = $exercise['title'];
        $this->originPrice = $exercise['originPrice'];
        $this->maxRate = $exercise['maxRate'];
        $this->productEnable = 'published' === $exercise['status'] ? true : false;
        $this->cover = [
            'small' => empty($exercise['cover']['small']) ? '' : $exercise['cover']['small'],
            'middle' => empty($exercise['cover']['middle']) ? '' : $exercise['cover']['middle'],
            'large' => empty($exercise['cover']['large']) ? '' : $exercise['cover']['large'],
        ];
    }

    public function validate()
    {
        $access = $this->getExerciseService()->canJoinExercise($this->targetId);

        if (AccessorInterface::SUCCESS !== $access['code']) {
            throw OrderPayCheckException::UNPURCHASABLE_PRODUCT();
        }
    }

    public function onPaid($orderItem)
    {
        $order = $this->getOrderService()->getOrder($orderItem['order_id']);
        $info = [
            'orderId' => $order['id'],
            'remark' => $order['created_reason'],
            'reason' => OperateReason::JOIN_BY_PURCHASE,
            'reasonType' => OperateReason::JOIN_BY_PURCHASE_TYPE,
        ];

        try {
            if (!$this->getExerciseMemberService()->isExerciseMember($orderItem['target_id'], $orderItem['user_id'])) {
                $member = $this->getExerciseMemberService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
            }

            return OrderStatusCallback::SUCCESS;
        } catch (\Exception $e) {
            $this->getLogService()->error('order', 'item_bank_exercise_callback', 'order.item_bank_exercise_callback.fail',
                ['error' => $e->getMessage(), 'context' => $orderItem]);

            return false;
        }
    }

    public function onOrderRefundAuditing($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getExerciseMemberService()->lockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundCancel($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $this->getExerciseMemberService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
    }

    public function onOrderRefundRefunded($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];

        $member = $this->getExerciseMemberService()->getExerciseMember($orderItem['target_id'], $orderItem['user_id']);
        if (!empty($member)) {
            $info = [
                'reason' => $orderRefundItem['order_refund']['reason'],
                'reason_type' => 'refund',
            ];
            $this->getExerciseMemberService()->removeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
        }

        $this->updateMemberRecordByRefundItem($orderItem);
    }

    public function onOrderRefundRefused($orderRefundItem)
    {
        $orderItem = $orderRefundItem['order_item'];
        $exercise = $this->getExerciseService()->get($orderItem['target_id']);
        if (!empty($exercise)) {
            $this->getExerciseMemberService()->unlockStudent($orderItem['target_id'], $orderItem['user_id']);
        }
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }
}
