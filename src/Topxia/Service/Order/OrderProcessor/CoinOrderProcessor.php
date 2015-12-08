<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router = "";

    public function getRouter()
    {
        return $this->router;
    }

    public function preCheck($targetId, $userId)
    {
    }

    public function getOrderInfo($targetId, $fields)
    {
    }

    public function shouldPayAmount($targetId, $priceType, $cashRate, $coinEnabled, $fields)
    {
    }

    public function createOrder($orderInfo, $fields)
    {
    }

    protected function getTotalPrice($targetId, $priceType)
    {
    }

    public function doPaySuccess($success, $order)
    {
    }

    public function getOrderBySn($sn)
    {
        return $this->getCashOrdersService()->getOrderBySn($sn);
    }

    public function getOrderInfo($order)
    {
        $orderInfo = $this->getCashOrdersService()->getOrder($order['id']);
        return $orderInfo;
    }

    public function updateOrder($id, $fileds)
    {
        return $this->getCashOrdersService()->updateOrder($id, $fileds);
    }

    public function requestParams($order, $container)
    {
        $requestParams = array(
            'returnUrl' => $container->get('router')->generate('coin_order_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $container->get('router')->generate('coin_order_pay_notify', array('name' => $order['payment']), true),
            'showUrl'   => $container->get('router')->generate('my_coin', array(), true)
        );
        return $requestParams;
    }

    public function getNote($targetId)
    {
        $order = $this->getCashOrdersService()->getOrder($targetId);
        return str_replace(' ', '', strip_tags($order['note']));
    }

    public function getTitle($targetId)
    {
        $order = $this->getCashOrdersService()->getOrder($targetId);
        return str_replace(' ', '', strip_tags($order['title']));
    }

    protected function getCashOrdersService()
    {
        return ServiceKernel::instance()->createService('Cash.CashOrdersService');
    }

}
