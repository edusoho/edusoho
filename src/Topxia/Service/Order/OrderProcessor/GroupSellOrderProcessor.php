<?php
namespace Topxia\Service\Order\OrderProcessor;

use Exception;
use Topxia\Common\NumberToolkit;
use Topxia\Service\Common\ServiceKernel;

class GroupSellOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router    = "";
    protected $orderType = "groupSell";

    public function preCheck($targetId, $userId)
    {
        $group = $this->getGroupSellService()->getGroupSell($targetId);

        if (empty($group)) {
            return array("error" => "找不到要购买的组合!");
        }

        if ($group['startTime'] > time()) {
            return array("error" => "活动还没有开始");
        }

        if ($group['endTime'] < time()) {
            return array("error" => "活动已经结束");
        }

        return array();
    }

    public function getOrderInfo($targetId, $fields)
    {
        $group = $this->getGroupSellService()->getGroupSell($targetId);

        if (empty($group)) {
            throw new Exception("找不到要购买的组合!");
        }

        //组合购买不支持使用虚拟币等其他形式
        return array(
            'totalPrice' => $group["groupPrice"],
            'targetId'   => $targetId,
            'targetType' => $this->orderType,
            'showCoupon' => false,
            "group"      => $group
        );
    }

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
    {
        $totalPrice = $this->getTotalPrice($targetId, $priceType);

        $amount = $totalPrice;

        $totalPrice = NumberToolkit::roundUp($totalPrice);
        $amount     = NumberToolkit::roundUp($amount);

        return array(
            $amount,
            $totalPrice,
            null
        );
    }

    public function createOrder($orderInfo, $fields)
    {
        return $this->getGroupSellOrderService()->createOrder($orderInfo);
    }

    protected function getTotalPrice($targetId, $priceType)
    {
        $totalPrice = 0;
        $group      = $this->getGroupSellService()->getGroupSell($targetId);

        $totalPrice = (float) $group['groupPrice'];
        return $totalPrice;
    }

    public function doPaySuccess($success, $order)
    {
        if (!$success) {
            return;
        }

        $this->getGroupSellOrderService()->doSuccessPayOrder($order['id']);

        return;
    }

    public function getOrderBySn($sn)
    {
        return $this->getOrderService()->getOrderBySn($sn);
    }

    public function updateOrder($id, $fileds)
    {
        return $this->getOrderService()->updateOrder($id, $fileds);
    }

    public function getNote($targetId)
    {
        $group = $this->getGroupSellService()->getGroupSell($targetId);
        return $group['about'];
    }

    public function getTitle($targetId)
    {
        $group = $this->getGroupSellService()->getGroupSell($targetId);
        return $group['title'];
    }

    public function isTargetExist($targetId)
    {
        $group = $this->getGroupSellService()->getGroupSell($targetId);

        if (empty($group) || ($group['startTime'] > time() || $group['endTime'] < time())) {
            return false;
        }

        return true;
    }

    protected function getGroupSellService()
    {
        return ServiceKernel::instance()->createService('GroupSell:GroupSell.GroupSellService');
    }

    public function pay($payData)
    {
        return $this->getPayCenterService()->pay($payData);
    }

    public function callbackUrl($order, $container)
    {
        $group      = $this->getGroupSellService()->getGroupSell($order['targetId']);
        $targetType = $group['type'];

        if ($targetType == 'course') {
            $router = "my_courses_learning";
        } elseif ($targetType == 'classroom') {
            $router = "my_classrooms";
        } else {
            $router = "homepage";
        }

        $goto = $container->get('router')->generate($router, array(), true);
        return $goto;
    }

    public function cancelOrder($id, $message, $data)
    {
        return $this->getOrderService()->cancelOrder($id, $message, $data);
    }

    public function createPayRecord($id, $payData)
    {
        return $this->getOrderService()->createPayRecord($id, $payData);
    }

    public function generateOrderToken()
    {
        return 'c'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function getOrderInfoTemplate()
    {
        return "GroupSellBundle:GroupSell:orderInfo";
    }

    protected function getGroupSellOrderService()
    {
        return ServiceKernel::instance()->createService('GroupSell:GroupSell.GroupSellOrderService');
    }

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }

    protected function getPayCenterService()
    {
        return ServiceKernel::instance()->createService('PayCenter.PayCenterService');
    }
}
