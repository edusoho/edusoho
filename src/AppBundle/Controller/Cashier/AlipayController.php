<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlipayController extends BaseController
{
    public function payAction($trade)
    {
        $trade['pay_type'] = 'Web';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'alipay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_alipay_return', array(), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        return $this->redirect($result['platform_created_result']['url']);
    }

    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }

    public function returnAction(Request $request)
    {
        $data = $request->query->all();
        $this->getPayService()->notifyPaid('alipay.in_time', $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['out_trade_no']), true));
    }

    public function returnForAppAction(Request $request)
    {
        return new Response("<script type='text/javascript'>window.location='objc://alipayCallback?1';</script>");
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
