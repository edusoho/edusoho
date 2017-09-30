<?php

namespace AppBundle\Controller\Cashier;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AlipayController extends PaymentController
{
    public function pcPayAction($trade)
    {
        $trade['platform_type'] = 'Web';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'alipay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'alipay'), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->createJsonResponse(array(
                'isPaid' => 1,
                'redirectUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])),
            ));
        }

        return $this->createJsonResponse(array(
            'isPaid' => 0,
            'redirectUrl' => $result['platform_created_result']['url'],
        ));
    }

    public function mobilePayAction($trade)
    {
        $trade['platform_type'] = 'Wap';
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'alipay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'alipay'), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        return $this->redirect($result['platform_created_result']['url']);
    }

    public function notifyAction(Request $request, $payment)
    {
        $data = $request->request->all();
        $data['platform_type'] = 'Web';
        $result = $this->getPayService()->notifyPaid($payment, $data);

        return $this->createJsonResponse($result);
    }

    public function returnAction(Request $request, $payment)
    {
        $data = $request->query->all();
        $data['platform_type'] = 'Web';
        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['out_trade_no']), true));
    }

    public function returnForAppAction(Request $request)
    {
        $data = $request->query->all();
        $data['platform_type'] = 'Wap';

        try {
            $this->getPayService()->notifyPaid('alipay', $data);

            return new Response("<script type='text/javascript'>window.location='objc://alipayCallback?1';</script>");
        } catch (\Exception $e) {
            return new Response("<script type='text/javascript'>window.location='objc://alipayCallback?0';</script>");
        }
    }
}
