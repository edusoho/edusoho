<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;

class MobileOrderController extends MobileController
{

    public function payCourseAction(Request $request)
    {
        $result = array("status"=>"error");
        $token = $this->getUserToken($request);
        $formData = $request->query->all();

        $user = $this->getCurrentUser();

        if (empty($user)) {
            $result['message'] = "用户未登录，创建课程订单失败。";
            return $this->createJson($request, $this->result); 
        }

        $userInfo = ArrayToolkit::parts($formData, array(
            'truename',
            'mobile',
            'qq',
            'company',
            'job'
        ));
        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);

        if (!ArrayToolkit::requireds($formData, array('courseId', 'payment'))) {
            $result['message'] = "用订单数据缺失，创建课程订单失败";
            return $this->createJson($request, $result);
        }

        $course = $this->getCourseService()->getCourse($formData['courseId']);
        if (empty($course)) {
            $this->result['status'] = "error";
            $this->result['message'] = "课程不存在，创建课程订单失败。";
            return $this->createJson($request, $result);
        }

        $order = array();

        $order['userId'] = $user['id'];
        $order['title'] = "购买课程《{$course['title']}》";
        $order['targetType'] = 'course';
        $order['targetId'] = $course['id'];
        $order['payment'] = $formData['payment'];
        $order['amount'] = $course['price'];
        $order['snPrefix'] = 'C';

        if (!empty($formData['coupon'])) {
            $order['couponCode'] = $formData['coupon'];
        }

        if (!empty($formData['note'])) {
            $order['data'] = array('note' => $formData['note']);
        }

        $order = $this->getOrderService()->createOrder($order);
        if (intval($order['amount']*100) > 0) {
            $payRequestParams = array(
                'returnUrl' => $this->generateUrl('course_order_pay_return', array('name' => $order['payment']), true),
                'notifyUrl' => $this->generateUrl('course_order_pay_notify', array('name' => $order['payment']), true),
                'showUrl' => $this->generateUrl('course_show', array('id' => $course['id']), true),
            );
            //跳转支付宝支付
        } else {
            //课程价格为0
            list($success, $order) = $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time()
            ));

            $info = array(
                'orderId' => $order['id'],
                'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
            );
            $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);
        }
        $result['status'] = "success";
        return $this->createJson($request, $result);
    }

    public function refundCourseAction(Request $request , $course_id)
    {
        $result = array("status"=>"error");
        $token = $this->getUserToken($request);
        list($course, $member) = $this->getCourseService()->tryTakeCourse($course_id);
        $user = $this->getCurrentUser();

        if (empty($member) or empty($member['orderId'])) {
            $result['message'] = "您不是课程的学员或尚未购买该课程，不能退学。";
            return $this->createJson($request, $result);
        }

        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            $result['message'] = "课程不存在";
            return $this->createJson($request, $result);
        }

        $maxRefundDays = (int) $this->setting('refund.maxRefundDays', 0);
        $refundOverdue = (time() - $order['createdTime']) > ($maxRefundDays * 86400);

        if ('GET' == $request->getMethod()) {
            $data = $request->query->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $refund = $this->getOrderService()->applyRefundOrder($member['orderId'], $amount, $reason);
            if ($refund['status'] == 'created') {
                $this->getCourseService()->lockStudent($order['targetId'], $order['userId']);
                $message = $this->setting('refund.applyNotification', '');
                if ($message) {
                    $courseUrl = $this->generateUrl('course_show', array('id' => $course['id']));
                    $variables = array(
                        'course' => "<a href='{$courseUrl}'>{$course['title']}</a>"
                    );
                    $message = StringToolkit::template($message, $variables);
                    $this->getNotificationService()->notify($refund['userId'], 'default', $message);
                }
            } elseif ($refund['status'] == 'success') {
                $this->getCourseService()->removeStudent($order['targetId'], $order['userId']);
                $result['status'] = "success";
            }
            return $this->createJson($request, $result);
        }
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
