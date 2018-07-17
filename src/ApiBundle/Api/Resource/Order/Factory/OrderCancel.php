<?php

namespace ApiBundle\Api\Resource\Order\Factory;

use Biz\Order\OrderException;

class OrderCancel extends BaseOrder
{
    public function setOrderCanceled($sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        if (!$order) {
            throw OrderException::NOTFOUND_ORDER();
        } elseif ('closed' == $order['status']) {
            throw OrderException::CLOSED_ORDER();
        }
        $userId = $this->getCurrentUser()->getId();
        if ($this->getCurrentUser()->isAdmin() || $userId == $order['user_id']) {
            return $this->getWorkflowService()->close($order['id'], array('type' => 'manual'));
        } else {
            throw OrderException::BEYOND_AUTHORITY();
        }
    }
}
