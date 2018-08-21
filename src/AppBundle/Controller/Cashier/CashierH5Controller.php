<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CashierH5Controller extends BaseController
{
    public function redirectAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        return $this->redirect($trade['platform_created_result']['url']);
    }

    public function successAction(Request $request)
    {
        $tradeSn = $request->query->get('trade_sn');
        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        return $this->forward("AppBundle:Cashier/CashierH5:{$trade['type']}Success", array(
            'trade' => $trade,
        ));
    }

    public function rechargeSuccessAction($trade)
    {
        return $this->redirect($this->generateUrl('my_coin'));
    }

    public function purchaseSuccessAction($trade)
    {
        $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);

        $items = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $item1 = reset($items);

        $params = array(
            'targetId' => $item1['target_id'],
            'num' => $item1['num'],
            'unit' => $item1['unit'],
        );
        $product = $this->getOrderFacadeService()->getOrderProduct($item1['target_type'], $params);

        return $this->redirect($this->generateUrl($product->successUrl[0], $product->successUrl[1]));
    }

    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
