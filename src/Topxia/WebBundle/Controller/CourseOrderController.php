<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class CourseOrderController extends BaseController
{

    public function buyAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourseService()->getCourse($id);

        $data = array('courseId' => $course['id'], 'payment' => 'alipay');
        $form = $this->createNamedFormBuilder('course_order', $data)
            ->add('courseId', 'hidden')
            ->add('payment', 'hidden')
            ->getForm();

        return $this->render('TopxiaWebBundle:CourseOrder:buy-modal.html.twig', array(
            'course' => $course,
            'form' => $form->createView()
        ));
    }

    public function payAction(Request $request)
    {
        // var_dump($request->request->all());exit();
        $order = $this->getOrderService()->createOrder($request->request->all());

        if (intval($order['price']*100) > 0) {
            $paymentRequest = $this->createPaymentRequest($order);

            return $this->render('TopxiaWebBundle:CourseOrder:pay.html.twig', array(
                'form' => $paymentRequest->form(),
                'order' => $order,
            ));
        } else {
            $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['price'], 
                'paidTime' => time()
            ));

            return $this->redirect($this->generateUrl('course_show', array('id' => $order['courseId'])));
        }
    }

    public function payReturnAction(Request $request, $name)
    {
        $this->getLogService()->info('info', "{$name}页面跳转支付通知：" . json_encode($request->query->all()));
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();
        $order = $this->getOrderService()->payOrder($payData);

        return $this->redirect($this->generateUrl('course_show', array('id' => $order['courseId'])));
    }

    public function payNotifyAction(Request $request)
    {
        $this->getLogService()->info('info', "{$name}服务器端支付通知：" . json_encode($request->request->all()));
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();
        try {
            $order = $this->getOrderService()->payOrder($payData);
            return new Response('success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createPaymentRequest($order)
    {

        $options = $this->getPaymentOptions($order['payment']);
        $request = Payment::createRequest($order['payment'], $options);

        return $request->setParams(array(
            'orderSn' => $order['sn'],
            'title' => $order['title'],
            'summary' => '',
            'amount' => $order['price'],
            'returnUrl' => $this->generateUrl('course_order_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('course_order_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('course_show', array('id' => $order['courseId']), true),
        ));
    }

    private function createPaymentResponse($name, $params)
    {
        $options = $this->getPaymentOptions($name);
        $response = Payment::createResponse($name, $options);

        return $response->setParams($params);
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
        );

        return $options;
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}