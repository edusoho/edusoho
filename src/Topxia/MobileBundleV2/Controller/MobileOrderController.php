<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Component\HttpFoundation\Request;
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

        return $this->forward('TopxiaWebBundle:PayCenter:submitPayRequest', array(
            'order' => $order,
        ));
    }

    public function refundCourseAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if (empty($member) || empty($member['orderId'])) {
            return $this->createErrorResponse($request, 'not_member', '您不是课程的学员或尚未购买该课程，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);

        if (empty($order)) {
            return $this->createErrorResponse($request, 'order_error', '订单不存在，不能退学。');
        }

        $data = $request->query->all();
        $reason = empty($data['reason']) ? array() : $data['reason'];

        $refund =$this->getLocalOrderRefundService()->applyOrderRefund($order['id'], array('reason' => $reason));

        return $this->createJson($request, $refund);
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getLocalOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }
}
