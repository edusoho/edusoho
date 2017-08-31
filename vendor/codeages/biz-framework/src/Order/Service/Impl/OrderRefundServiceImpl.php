<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
    public function searchRefunds($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefunds($conditions)
    {
        return $this->getOrderRefundDao()->count($conditions);
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }
}