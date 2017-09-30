<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Common\DeviceToolkit;
use Symfony\Component\HttpFoundation\Request;

class LianlianpayController extends PaymentController
{
    public function pcPayAction($trade)
    {
        $result = $this->createTrade($trade);

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
        $result = $this->createTrade($trade);

        if ($result['status'] == 'paid') {
            return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $result['trade_sn'])));
        }

        return $this->redirect($result['platform_created_result']['url']);
    }

    protected function createTrade($trade)
    {
        $user = $this->getUser();
        $trade['platform_type'] = $this->isMobileClient() ? 'Wap' : 'Web';
        $trade['attach']['user_created_time'] = $user['createdTime'];
        $trade['attach']['identify_user_id'] = $this->getIdentify().'_'.$user['id'];
        $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'lianlianpay'), true);
        $trade['return_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'lianlianpay'), true);
        $trade['show_url'] = $this->generateUrl('cashier_pay_return', array('payment' => 'lianlianpay'), true);

        return $this->getPayService()->createTrade($trade);
    }

    public function notifyAction(Request $request, $payment)
    {
        $returnArray = json_decode(file_get_contents('php://input'), true);
        $result = $this->getPayService()->notifyPaid($payment, $returnArray);

        return $this->createJsonResponse($result);
    }

    public function mobileReturnAction(Request $request)
    {
        $data = $request->request->all();
        $data = json_decode($data['res_data'], true);
        $this->getPayService()->notifyPaid('lianlianpay', $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), true));
    }

    public function returnAction(Request $request, $payment)
    {
        if (DeviceToolkit::isMobileClient()) {
            return $this->forward('AppBundle:Cashier/Lianlianpay:mobileReturn');
        }

        $data = $request->request->all();

        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), true));
    }

    protected function getIdentify()
    {
        $identify = $this->getSettingService()->get('llpay_identify');
        if (empty($identify)) {
            $identify = substr(md5(uniqid()), 0, 12);
            $this->getSettingService()->set('llpay_identify', $identify);
        }

        return $identify;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
