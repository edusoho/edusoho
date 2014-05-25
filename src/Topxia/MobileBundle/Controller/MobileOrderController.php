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

        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '用户未登录，创建课程订单失败。');
        }

        $order = $this->getCourseOrderService()->createOrder($formData);

        if ($order['status'] == 'paid') {
            $result = array('status' => 'ok', 'paid' => true);
        } else {
            $result = array('status' => 'ok', 'paid' => false, 'payUrl' => MobileAlipayConfig::createAlipayOrderUrl($request, "edusoho", $order));
        }

        return $this->createJson($request, $result);

    }

    public function refundCourseAction(Request $request , $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        
        if (empty($member) or empty($member['orderId'])) {
            return $this->createErrorResponse('not_member', '您不是课程的学员或尚未购买该课程，不能退学。');
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            return $this->createErrorResponse('order_error', '订单不存在，不能退学。');
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
