<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;

class CourseOrderController extends OrderController
{
    public $courseId = 0;

    public function buyAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $courseSetting = $this->getSettingService()->get('course', array());

        $userInfo = $this->getUserService()->getUserProfile($user['id']);
        $userInfo['approvalStatus'] = $user['approvalStatus'];

        $course = $this->getCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseOrder:buy-modal.html.twig', array(
            'course' => $course,
            'payments' => $this->getEnabledPayments(),
            'user' => $userInfo,
            'avatarAlert' => AvatarAlert::alertJoinCourse($user),
            'courseSetting' => $courseSetting,
        ));
    }

    public function payAction(Request $request)
    {

        $formData = $request->request->all();

        $courseId = $formData['courseId'];

        $user = $this->getCurrentUser();
        if (empty($user)) {
            return $this->createMessageResponse('error', '用户未登录，创建课程订单失败。');
        }

        $userInfo = ArrayToolkit::parts($formData, array(
            'truename',
            'mobile',
            'qq',
            'company',
            'job'
        ));

        $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);     


        $mTookeenCookie = isset($_COOKIE["mu"]) ?$_COOKIE["mu"] : null;

        $mTookeenCookie = isset($_COOKIE["mc".$courseId]) ?$_COOKIE["mc".$courseId] : $mTookeenCookie;

        if (!empty($mTookeenCookie)){
          
             $formData['mTookeen'] = $mTookeenCookie;

        }
       
        if (!ArrayToolkit::requireds($formData, array('courseId', 'payment'))) {
            return $this->createMessageResponse('error', '订单数据缺失，创建课程订单失败。');
        }

        $course = $this->getCourseService()->getCourse($formData['courseId']);
        if (empty($course)) {
            return $this->createMessageResponse('error', '课程不存在，创建课程订单失败。');
        }

        $order = array();

        $order['userId'] = $user->id;
        $order['title'] = "购买课程《{$course['title']}》";
        $order['targetType'] = 'course';
        $order['targetId'] = $course['id'];
        $order['payment'] = $formData['payment'];
        $order['amount'] = $course['price'];
        $order['snPrefix'] = 'C';

        if (!empty($formData['coupon'])) {
            $order['couponCode'] = $formData['coupon'];
        }

        if (!empty($formData['promoCode'])) {
            $order['promoCode'] = $formData['promoCode'];
        }

        if (!empty($formData['mTookeen'])) {
            $order['mTookeen'] = $formData['mTookeen'];
        }

        if (!empty($formData['promoCode'])) {
            $order['promoCode'] = $formData['promoCode'];
        }


        $order = $this->getOrderService()->createOrder($order);


        if (intval($order['amount']*100) > 0) {

            $payRequestParams = array(
                'returnUrl' => $this->generateUrl('course_order_pay_return', array('name' => $order['payment']), true),
                'notifyUrl' => $this->generateUrl('course_order_pay_notify', array('name' => $order['payment']), true),
                'showUrl' => $this->generateUrl('course_show', array('id' => $course['id']), true),
            );

            return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
                'order' => $order,
                'requestParams' => $payRequestParams,
            ));
        } else {
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

            return $this->redirect($this->generateUrl('course_show', array('id' => $order['targetId'])));
        }
    }

    public function payReturnAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayReturn($request, $name, function($success, $order) use(&$controller) {
            if (!$success) {
                $controller->generateUrl('course_show', array('id' => $order['targetId']));
            }

            if ($order['targetType'] != 'course') {
                throw \RuntimeException('非课程订单，加入课程失败。');
            }

            $info = array(
                'orderId' => $order['id'],
                'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
            );

            if (!$controller->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
                $controller->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);

                $controller->getCourseService()->incomeCourse($order['targetId'],'income',$order['amount']);

            }

            return $controller->generateUrl('course_show', array('id' => $order['targetId']));
        });
    }

    public function payNotifyAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayNotify($request, $name, function($success, $order) use(&$controller) {
            if (!$success) {
                return ;
            }
            if ($order['targetType'] != 'course') {
                throw \RuntimeException('非课程订单，加入课程失败。');
            }

            $info = array(
                'orderId' => $order['id'],
                'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
            );

            if (!$controller->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
                $controller->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);
                $controller->getCourseService()->incomeCourse($order['targetId'],'income',$order['amount']);

            }

            return ;
        });
    }

    public function refundAction(Request $request , $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $user = $this->getCurrentUser();

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

        $this->getCourseOrderService()->cancelRefundOrder($member['orderId']);

        return $this->createJsonResponse(true);

    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

    private function getOffsaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffsaleService');
    }


    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName . '_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName . '_type']) ? '' : $setting[$payName . '_type'],
                );
            }
        }

        return $enableds;
    }

}