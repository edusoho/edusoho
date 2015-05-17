<?php
namespace Topxia\Service\Order\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class CancelOrderJob implements Job
{
	public function execute($params)
    {
    	$conditions = array(
    		"status" => "created",
    		"createdTime_LT" => time()-48*60*60
    	);

        $orders = $this->getOrderService()->searchOrders($conditions, $sort = 'latest', 0, 10);
        foreach ($orders as $key => $order) {
        	$this->getOrderService()->cancelOrder($order["id"]);
        }

    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
