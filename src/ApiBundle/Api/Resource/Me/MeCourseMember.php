<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ApiException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Biz\Order\Service\OrderService;

class MeCourseMember extends AbstractResource
{
    public function remove(ApiRequest $request, $courseId)
    {
        $reason = $request->request->get('reason', '从App退出课程');
        $processor = OrderRefundProcessorFactory::create('course');

        $user = $this->getCurrentUser();
        $member = $processor->getTargetMember($courseId, $user['id']);

        if (empty($member) || empty($member['orderId'])) {
            throw new ApiException('您不是学员或尚未购买，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);

        if (empty($order)) {
            throw new ApiException();
        }

        if ($order['targetType'] == 'groupSell') {
            throw new ApiException('组合购买课程不能退出。');
        }

        $processor->applyRefundOrder($member['orderId'], 0, array('note' => $reason), null);

        return array('success' => true);
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }
}