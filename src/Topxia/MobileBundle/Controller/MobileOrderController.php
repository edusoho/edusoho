<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\MobileBundle\Alipay\MobileAlipayConfig;
use Symfony\Component\HttpFoundation\Response;

class MobileOrderController extends MobileController
{

    public function payCourseAction(Request $request, $courseId)
    {
        $formData = $request->query->all();
        $formData['courseId'] = $courseId;

        $token = $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建课程订单失败。');
        }

        $order = $this->getCourseOrderService()->createOrder($formData);

        if ($order['status'] == 'paid') {
            $result = array('status' => 'ok', 'paid' => true);
        } else {
            $payment = $this->setting('payment', array());
            if (empty($payment['enabled'])) {
                return $this->createJson($request, array('status' => 'error', 'message' => '支付功能未开启！'));
            }

            if (empty($payment['alipay_enabled'])) {
                return $this->createJson($request, array('status' => 'error', 'message' => '支付宝支付未开启！'));
            }

            if (empty($payment['alipay_key']) or empty($payment['alipay_secret']) or empty($payment['alipay_account'])) {
                return $this->createJson($request, array('status' => 'error', 'message' => '支付宝参数不正确！'));
            }

            if (empty($payment['alipay_type']) or $payment['alipay_type'] != 'direct') {
                $result = array('status' => 'ok', 'paid' => false, 'payUrl' => $this->generateUrl('mapi_order_submit_pay_request', array('id' => $order['id'], 'token' => $token['token']), true));
            } else {
                $result = array('status' => 'ok', 'paid' => false, 'payUrl' => MobileAlipayConfig::createAlipayOrderUrl($request, "edusoho", $order));
            }

        }

        return $this->createJson($request, $result);
    }

    public function submitPayRequestAction(Request $request, $id)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        $order = $this->getOrderService()->getOrder($id);
        if (empty($order)) {
            return new Response('订单不存在！');
        }

        if ($order['userId'] != $user['id']) {
            return new Response('该订单，你不能支付！');
        }

        if ($order['status'] != 'created') {
            return new Response('该订单状态下，不能支付！');
        }

        $payRequestParams = array(
            'returnUrl' => $this->generateUrl('course_order_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('course_order_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('course_show', array('id' => $order['targetId']), true),
        );

        return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
            'order' => $order,
            'requestParams' => $payRequestParams,
        ));
    }

    public function refundCourseAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        
        if (empty($member) or empty($member['orderId'])) {
            return $this->createErrorResponse($request, 'not_member', '您不是课程的学员或尚未购买该课程，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            return $this->createErrorResponse($request, 'order_error', '订单不存在，不能退学。');
        }

        $data = $request->query->all();
        $reason = empty($data['reason']) ? array() : $data['reason'];
        $amount = empty($data['applyRefund']) ? 0 : null;

        $refund = $this->getCourseOrderService()->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);

        return $this->createJson($request, $refund);
    }

    protected function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

     protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
