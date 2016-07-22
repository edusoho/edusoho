<?php
namespace Topxia\Service\Order\OrderProcessor;

use Topxia\Service\Common\ServiceKernel;

class CoinOrderProcessor extends BaseProcessor implements OrderProcessor
{
    protected $router = "my_coin";

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

    public function updateOrder($id, $fileds)
    {
        return $this->getCashOrdersService()->updateOrder($id, $fileds);
    }

    public function getNote($targetId)
    {
        $coin = $this->getSettingService()->get('coin');
        return '充值'.$coin['coin_name'];
    }

    public function getTitle($targetId)
    {
        $coin = $this->getSettingService()->get('coin');
        return $coin['coin_name'];
    }

    public function pay($payData)
    {
        return $this->getCashOrdersService()->payOrder($payData);
    }

    public function cancelOrder($id, $message, $data)
    {
        return $this->getCashOrdersService()->cancelOrder($id, $message, $data);
    }

    public function createPayRecord($id, $payData)
    {
        return $this->getCashOrdersService()->createPayRecord($id, $payData);
    }

    public function generateOrderToken()
    {
        return 'o'.date('YmdHis', time()).mt_rand(10000, 99999);
    }

    public function getOrderInfoTemplate()
    {
        return "ChargeCoinBundle:Coin:orderInfo";
    }

    public function isTargetExist($targetId)
    {
        return true;
    }

    public function callbackUrl($order, $container)
    {
        $goto = $container->get('router')->generate('my_coin', array(), true);
        return $goto;
    }

    protected function getCashOrdersService()
    {
        return ServiceKernel::instance()->createService('Cash.CashOrdersService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

}
