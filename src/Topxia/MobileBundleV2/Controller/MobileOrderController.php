<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\MobileBundleV2\Alipay\MobileAlipayConfig;
use Symfony\Component\HttpFoundation\Response;

class MobileOrderController extends MobileBaseController
{

    public function submitPayRequestAction(Request $request, $id)
    {
        $user = $this->getUserByToken($request);

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
            'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('pay_success_show', array('id' => $order['id']), true),
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

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

}
