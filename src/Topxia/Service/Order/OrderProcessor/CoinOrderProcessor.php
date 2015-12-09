<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class CoinOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router = "my_coin";

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

    public function getOrderMessage($order)
    {
        $orderInfo             = $this->getCashOrdersService()->getOrder($order['id']);
        $orderInfo['template'] = 'coin';
        return $orderInfo;
    }

    public function updateOrder($id, $fileds)
    {
        return $this->getCashOrdersService()->updateOrder($id, $fileds);
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

    public function pay($payData)
    {
        return $this->getCashOrdersService()->payOrder($payData);
    }

    public function callbackUrl($router, $order, $container)
    {
        $goto = !empty($router) ? $container->get('router')->generate($router) : $this->generateUrl('homepage', array(), true);
        return $goto;
    }

    public function cancelOrder($id, $message, $data)
    {
        return $this->getCashOrdersService()->cancelOrder($id, $message, $data);
    }

    public function createPayRecord($id, $payData)
    {
        return $this->getCashOrdersService()->createPayRecord($id, $payData);
    }

    protected function getCashOrdersService()
    {
        return ServiceKernel::instance()->createService('Cash.CashOrdersService');
    }

}
