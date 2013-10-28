<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
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
        $this->getLogService()->info('order', 'pay_result',  "{$name}页面跳转支付通知", $request->query->all());
        $response = $this->createPaymentResponse($name, $request->query->all());

        $payData = $response->getPayData();
        $order = $this->getOrderService()->payOrder($payData);

        return $this->redirect($this->generateUrl('course_show', array('id' => $order['courseId'])));
    }

    public function payNotifyAction(Request $request, $name)
    {
        $this->getLogService()->info('order', 'pay_result', "{$name}服务器端支付通知", $request->request->all());
        $response = $this->createPaymentResponse($name, $request->request->all());

        $payData = $response->getPayData();
        try {
            $order = $this->getOrderService()->payOrder($payData);
            return new Response('success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function refundAction(Request $request , $id)
    {
        $course = $this->getCourseService()->tryTakeCourse($id);
        $user = $this->getCurrentUser();

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);

        if (empty($member) or empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是课程的学员或尚未购买该课程，不能退学。');
        }


        $order = $this->getOrderService()->getOrder($member['orderId']);
        if (empty($order)) {
            throw $this->createNotFoundException();
        }


        $maxRefundDays = (int) $this->setting('refund.maxRefundDays', 0);
        $refundOverdue = (time() - $order['createdTime']) > ($maxRefundDays * 86400);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $refund = $this->getOrderService()->applyRefundOrder($member['orderId'], $amount, $reason);
            if ($refund['status'] == 'created') {
                $message = $this->setting('refund.applyNotification', '');
                if ($message) {
                    $courseUrl = $this->generateUrl('course_show', array('id' => $course['id']));
                    $variables = array(
                        'course' => "<a href='{$courseUrl}'>{$course['title']}</a>"
                    );
                    $message = StringToolkit::template($message, $variables);
                    $this->getNotificationService()->notify($refund['userId'], 'default', $message);
                }
            }

            return $this->createJsonResponse($refund);
        }

        return $this->render('TopxiaWebBundle:CourseOrder:refund-modal.html.twig', array(
            'course' => $course,
            'order' => $order,
            'maxRefundDays' => $maxRefundDays,
            'refundOverdue' => $refundOverdue,
        ));
    }

    public function cancelRefundAction(Request $request , $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createAccessDeniedException();
        }

        $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
        if (empty($member) or empty($member['orderId'])) {
            throw $this->createAccessDeniedException('您不是课程的学员或尚未购买该课程，不能取消退款。');
        }

        $this->getOrderService()->cancelRefundOrder($member['orderId']);

        return $this->createJsonResponse(true);

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

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}