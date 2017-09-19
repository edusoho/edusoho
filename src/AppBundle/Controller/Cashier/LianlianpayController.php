<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

class LianlianpayController extends BaseController
{
    public function payAction($trade)
    {
        $user = $this->getUser();
        $trade['platform_type'] = 'Web';
        $trade['attach']['user_created_time'] = $user['createdTime'];
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'lianlianpay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'lianlianpay'), true);
        $result = $this->getPayService()->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        return $this->redirect($result['platform_created_result']['url']);
    }

    public function notifyAction(Request $request, $payment)
    {
        $returnArray = json_decode(file_get_contents('php://input'), true);
        $result = $this->getPayService()->notifyPaid($payment, $returnArray);

        return $this->createJsonResponse($result);
    }

    public function returnAction(Request $request, $payment)
    {
        $data = $request->request->all();
        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), true));
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
