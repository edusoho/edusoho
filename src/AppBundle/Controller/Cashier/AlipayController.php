<?php

namespace AppBundle\Controller\Cashier;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AlipayController extends PaymentController
{
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

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['out_trade_no']), UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function returnForH5Action(Request $request, $payment)
    {
        $data = $request->query->all();
        $data['platform_type'] = 'Wap';
        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success_for_h5', array('trade_sn' => $data['out_trade_no']), UrlGeneratorInterface::ABSOLUTE_URL));
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
