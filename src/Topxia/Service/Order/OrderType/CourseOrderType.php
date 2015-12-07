<?php
namespace Topxia\Service\Order\OrderType;

use Topxia\Service\Common\ServiceKernel;

class CourseOrderType extends BaseOrderType implements OrderType
{
    public function getOrderBySn($sn)
    {
        return $this->getOrderService()->getOrderBySn($sn);
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

}
