<?php

namespace Biz\Export\Order;

use AppBundle\Common\ArrayToolkit;
use Biz\Export\Exporter;

class OrderExport extends Exporter
{

    public function __construct($container, $conditions)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');

        $this->conditions = $this->buildExportCondition($conditions);
    }

    public function getCount()
    {
        return $this->getOrderService()->countOrders($this->conditions);
    }

    public function getTitles()
    {
        return array('订单号', '订单状态', '订单名称', '订单价格', '优惠码', '优惠金额', '虚拟币支付', '实付价格', '支付方式', '购买者', '姓名', '操作', '创建时间', '付款时间');
    }

    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getExportContent($start, $limit)
    {

    }

    private function buildExportCondition($conditions)
    {
        if (!empty($conditions['startTime']) && !empty($conditions['endTime'])) {
            $conditions['startTime'] = strtotime($conditions['startTime']);
            $conditions['endTime'] = strtotime($conditions['endTime']);
        }

        return $conditions;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }
}
