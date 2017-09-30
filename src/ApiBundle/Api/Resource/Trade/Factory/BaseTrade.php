<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use AppBundle\Common\MathToolkit;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

abstract class BaseTrade
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Biz
     */
    protected $biz;

    protected $payment = '';

    protected $platformType = '';

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function setBiz($biz)
    {
        $this->biz = $biz;
    }

    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    public function create($params)
    {
        $tradeFields = array(
            'type' => $params['type'],
            'goods_detail' => '',
            'price_type' => 'money',
            'user_id' => $params['userId'],
            'create_ip' => $params['clientIp'],
            'attach' => array(
                'user_id' => $params['userId'],
            ),
            'platform' => $this->payment,
            'platform_type' => $this->platformType,
            'notify_url' => $this->generateUrl('cashier_pay_notify', array('payment' => $this->payment), true)
        );

        if ($params['type'] == 'purchase' && !empty($params['orderSn'])) {
            $order = $this->getOrderService()->getOrderBySn($params['orderSn']);
            $tradeFields['amount'] = $order['pay_amount'];
            $tradeFields['order_sn'] = $order['sn'];
            $coinAmount = isset($params['coinAmount']) ? $params['coinAmount'] : 0;
            $tradeFields['coin_amount'] = MathToolkit::simple($coinAmount, 100);
            $cashAmount = $this->getOrderFacadeService()->getTradePayCashAmount($order, $coinAmount);
            $tradeFields['cash_amount'] = MathToolkit::simple($cashAmount, 100);
            $tradeFields['goods_title'] = $order['title'];
        }

        if ($params['type'] == 'recharge') {
            $tradeFields['goods_title'] = '虚拟币充值';
            $tradeFields['amount'] = $params['amount'];
            $tradeFields['cash_amount'] = $params['amount'];
        }

        $tradeFields = array_merge($tradeFields, $this->getCustomFields($params));
        $trade = $this->getPayService()->createTrade($tradeFields);

        return $trade;
    }


    public function getCustomFields($params)
    {
        return array();
    }

    public function createResponse($trade)
    {
        return array();
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->biz->service('OrderFacade:OrderFacadeservice');
    }
}