<?php

namespace AppBundle\Controller\Cashier;

use Symfony\Component\HttpFoundation\Request;

class WechatController extends PaymentController
{
    public function wechatJsPayAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');

        $trade = $this->getPayService()->getTradeByTradeSn($tradeSn);

        return $this->render(
            'cashier/wechat/h5.html.twig', array(
            'trade' => $trade,
        ));
    }

    public function returnAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $this->getPayService()->queryTradeFromPlatform($tradeSn);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $tradeSn)));
    }

    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }
}
