<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Common\DeviceToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LianlianpayController extends PaymentController
{
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

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function returnAction(Request $request, $payment)
    {
        if (DeviceToolkit::isMobileClient()) {
            return $this->forward('AppBundle:Cashier/Lianlianpay:mobileReturn');
        }

        $data = $request->request->all();

        $this->getPayService()->notifyPaid($payment, $data);

        return $this->redirect($this->generateUrl('cashier_pay_success', array('trade_sn' => $data['no_order']), UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
