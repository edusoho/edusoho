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
        $course = $this->getCourseService()->getCourse($id);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $previewAs = $request->query->get('previewAs');

        $member = $user ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;

        $member = $this->previewAsMember($previewAs, $member, $course);

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
            'member' => $member
        ));
    }

    public function payAction(Request $request)
    {
        $formData = $request->request->all();

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


        $order = $this->getCourseOrderService()->createOrder($formData);

        if ($order['status'] == 'paid') {
            return $this->redirect($this->generateUrl('course_show', array('id' => $order['targetId'])));
        } else {
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
    }

    public function noticeAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:CourseOrder:notice.html.twig');
    }

    public function payReturnAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayReturn($request, $name, function($success, $order) use(&$controller) {
            if (!$success) {
                $controller->generateUrl('course_show', array('id' => $order['targetId']));
            }

            $controller->getCourseOrderService()->doSuccessPayOrder($order['id']);

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

            $controller->getCourseOrderService()->doSuccessPayOrder($order['id']);

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

    private function previewAsMember($as, $member, $course)
    {
        $user = $this->getCurrentUser();
        if (empty($user->id)) {
            return null;
        }


        if (in_array($as, array('member', 'guest'))) {
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $member = array(
                    'id' => 0,
                    'courseId' => $course['id'],
                    'userId' => $user['id'],
                    'levelId' => 0,
                    'learnedNum' => 0,
                    'isLearned' => 0,
                    'seq' => 0,
                    'isVisible' => 0,
                    'role' => 'teacher',
                    'locked' => 0,
                    'createdTime' => time(),
                    'deadline' => 0
                );
            }

            if (empty($member) or $member['role'] != 'teacher') {
                return $member;
            }

            if ($as == 'member') {
                $member['role'] = 'student';
            } else {
                $member = null;
            }
        }

        return $member;
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
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