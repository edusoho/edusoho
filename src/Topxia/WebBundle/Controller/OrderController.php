<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Topxia\Service\Common\ServiceKernel;

class OrderController extends BaseController
{

    public function submitPayRequestAction(Request $request , $order)
    {
        echo 'hello';
        var_dump($_POST);exit();
        $paymentRequest = $this->createPaymentRequest($order);

        return $this->render('TopxiaWebBundle:CourseOrder:pay.html.twig', array(
            'form' => $paymentRequest->form(),
            'order' => $order,
        ));
    }

    private function createPaymentRequest($order)
    {

        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        return $request->setParams(array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['amount'],
            'returnUrl' => $this->generateUrl('course_order_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('course_order_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('course_show', array('id' => $order['courseId']), true),
        ));
    }

    private function getPaymentOptions($payment)
    {
        $settings = $this->setting('payment');

        if (empty($settings)) {
            throw new \RuntimeException('支付参数尚未配置，请先配置。');
        }

        if (empty($settings['enabled'])) {
            throw new \RuntimeException("支付模块未开启，请先开启。");
        }

        if (empty($settings[$payment. '_enabled'])) {
            throw new \RuntimeException("支付模块({$payment})未开启，请先开启。");
        }

        if (empty($settings["{$payment}_key"]) or empty($settings["{$payment}_secret"])) {
            throw new \RuntimeException("支付模块({$payment})参数未设置，请先设置。");
        }

        $options = array(
            'key' => $settings["{$payment}_key"],
            'secret' => $settings["{$payment}_secret"],
            'type' => $settings["{$payment}_type"]
        );

        return $options;
    }

}