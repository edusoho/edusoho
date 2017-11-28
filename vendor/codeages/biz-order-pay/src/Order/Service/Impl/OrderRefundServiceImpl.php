<?php

namespace Codeages\Biz\Order\Service\Impl;

use Codeages\Biz\Order\Dao\OrderItemRefundDao;
use Codeages\Biz\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function getOrderRefundById($id)
    {
        return $this->getOrderRefundDao()->get($id);
    }
    
    public function searchRefunds($conditions, $orderby, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefunds($conditions)
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderRefundDao()->count($conditions);
    }

    public function findRefundsByOrderIds($orderIds)
    {
        return $this->getOrderRefundDao()->findByOrderIds($orderIds);
    }

    public function searchRefundItems($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderItemRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefundItems($conditions)
    {
        return $this->getOrderItemRefundDao()->count($conditions);
    }

    public function findOrderItemRefundsByOrderRefundId($orderRefundId)
    {
        return $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefundId);
    }

    protected function filterConditions($conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($key == 'order_item_refund_title') {
                $customConditions['title_LIKE'] = $value;
                unset($conditions[$key]);
            }

            if ($key == 'order_item_refund_target_ids') {
                $customConditions['target_ids'] = $value;
                unset($conditions[$key]);
            }

            if ($key == 'order_item_refund_target_type') {
                $customConditions['target_type'] = $value;
                unset($conditions[$key]);
            }
        }

        if (!empty($customConditions)) {
            $conditions['ids'] = array(0);

            $itemResult = $this->getOrderItemRefundDao()->findByConditions($customConditions);
            if (!empty($itemResult)) {
                $ids = ArrayToolkit::column($itemResult, 'order_refund_id');
                $conditions['ids'] = $ids;
            }
        }

        return $conditions;
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    /**
     * @return OrderItemRefundDao
     */
    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }
}