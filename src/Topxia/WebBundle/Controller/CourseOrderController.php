<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class CourseOrderController extends OrderController
{
    public $courseId = 0;

    public function buyAction(Request $request, $id)
    {   
        $fields = $request->query->all();

        $coinPayAmount = 0;
        $totalMoneyPrice = 0;
        $totalCoinPrice = 0;

        $totalPrice = 0;
        $coinPreferentialPrice = 0;
        $shouldPayMoney = 0;

        $course = $this->getCourseService()->getCourse($id);
        $userIds = array();
        $userIds = array_merge($userIds, $course['teacherIds']);
        $users = $this->getUserService()->findUsersByIds($userIds);
        $totalMoneyPrice += $course["price"];
        $totalCoinPrice += $course["coinPrice"];

        $coinSetting = $this->getSettingService()->get("coin");
        
        $cashRate = 1;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $user = $this->getCurrentUser();
        $account = $this->getCashService()->getAccountByUserId($user["id"]);
        $accountCash = $account["cash"];

        if($totalMoneyPrice*100 > $accountCash/$cashRate*100) {
            $shouldPayMoney = $totalMoneyPrice - $accountCash/$cashRate;
            $coinPayAmount = $accountCash;
        } else {
            $coinPayAmount = $totalMoneyPrice*$cashRate;
        }

        $couponApp = $this->getAppService()->findInstallApp("Coupon");
        $vipApp = $this->getAppService()->findInstallApp("Vip");

        if(!empty($vipApp)) {
            $vip = $this->getVipService()->getMemberByUserId($user["id"]);
        }

        $coursePriceShowType = "RMB";
        if(array_key_exists("course_price_show_type", $coinSetting)) {
            $coursePriceShowType = $coinSetting["course_price_show_type"];
        }

        if($coursePriceShowType == "RMB") {
            $totalPrice = $totalMoneyPrice;
            $coinPreferentialPrice = $coinPayAmount/$cashRate;
        } else if ($coursePriceShowType == "Coin") {
            $totalPrice = $totalCoinPrice;
            $coinPreferentialPrice = $coinPayAmount;
        }

        return $this->render('TopxiaWebBundle:Order:order-create.html.twig', array(
            'courses' => empty($course) ? null : array($course),
            'users' => empty($users) ? null : $users,
            'account' => $account,
            'couponApp' => $couponApp,
            'vipApp' => $vipApp,
            'cashRate' => $cashRate,

            'totalPrice' => $totalPrice,
            'shouldPayMoney' => $shouldPayMoney,
            'coinPayAmount' => $coinPayAmount,
            'coinPreferentialPrice' => $coinPreferentialPrice,

            'targetIds' => array($id),
            'targetTypes' => array("course"),
            'coursePriceShowType' => $coursePriceShowType,

            'vip' => empty($vip) ? null : array($vip),
            'payUrl' => 'course_order_pay'
        ));
    }

    public function repayAction(Request $request)
    {
        $order = $this->getOrderService()->getOrder($request->request->get('orderId'));
        if (empty($order)) {
            return $this->createMessageResponse('error', '订单不存在!');
        }

        if ( (time() - $order['createdTime']) > 40 * 3600 ) {
            return $this->createMessageResponse('error', '订单已过期，不能支付，请重新创建订单。');
        }

        if ($order['targetType'] != 'course') {
            return $this->createMessageResponse('error', '此类订单不能支付，请重新创建订单!');
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);
        if (empty($course)) {
            return $this->createMessageResponse('error', '购买的课程不存在，请重新创建订单!');
        }

        if ($course['price'] != ($order['amount'] + $order['couponDiscount'])) {
            return $this->createMessageResponse('error', '订单价格已变更，请重新创建订单!');
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

    public function payAction(Request $request)
    {
        $fields = $request->request->all();
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，创建订单。');
        }

        if(!array_key_exists("targets", $fields)) {
            return $this->createMessageResponse('error', '订单中没有购买的内容，不能创建!');
        }
        $targets = $fields["targets"];
        
        $targetTypes = array();
        $targetIds = array();

        foreach ($targets as $key => $value) {
            $target = explode("-",$value);
            $targetTypes[] = $target[0];
            $targetIds[] = $target[1];
        }

        $coinSetting = $this->getSettingService()->get("coin");
        $coursePriceShowType = "RMB";
        if(array_key_exists("course_price_show_type", $coinSetting)) {
            $coursePriceShowType = $coinSetting["course_price_show_type"];
        }
        $cashRate = 1;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $totalPrice = 0;
        foreach ($targetTypes as $key => $value) {
            if("course" == $value) {
                $course = $this->getCourseService()->getCourse($targetIds[$key]);
                if($coursePriceShowType == "RMB") {
                    $totalPrice += $course["price"];
                } else if ($coursePriceShowType == "Coin") {
                    $totalPrice += $course["coinPrice"];
                }
            }
        }

        if($totalPrice != $fields["totalPrice"]) {
            return $this->createMessageResponse('error', '实际价格不匹配，不能创建订单!');
        }

        //虚拟币优惠价格
        $coinPayAmount = $fields["coinPayAmount"];
        $coinPreferentialPrice = 0;
        if($coursePriceShowType == "RMB") {
            $coinPreferentialPrice = $coinPayAmount/$cashRate;
        } else if ($coursePriceShowType == "Coin") {
            $coinPreferentialPrice = $coinPayAmount;
        }

        //优惠码优惠价格 TODO

        $amount = $totalPrice - $coinPreferentialPrice;
        // if($amount != $fields["actualPrice"]) {
        //     return $this->createMessageResponse('error', '支付价格不匹配，不能创建订单!');
        // }

        $cashRate = 1;
        if(array_key_exists("cash_rate", $coinSetting)) {
            $cashRate = $coinSetting["cash_rate"];
        }

        $order = array(
            'priceType' => $coursePriceShowType,
            'totalPrice' => $totalPrice,
            'amount' => $amount,
            'cashRate' => $cashRate,
            'coinAmount' => $coinPayAmount,
            'userId' => $user["id"],
            'payment' => 'alipay',
            'courseId' => $targetIds[0]
        );

        $order = $this->getCourseOrderService()->createOrder($order);

        return $this->redirect($this->generateUrl('pay_center_show', array('id' => $order['id'])));

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

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $refund = $this->getCourseOrderService()->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);

            return $this->createJsonResponse($refund);
        }

        $maxRefundDays = (int) $this->setting('refund.maxRefundDays', 0);
        $refundOverdue = (time() - $order['createdTime']) > ($maxRefundDays * 86400);

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

    private function getRemainStudentNum($course)
    {
        $remainingStudentNum = $course['maxStudentNum'];

        if ($course['type'] == 'live') {
            if ($course['price'] <= 0) {
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'];
            } else {
                $createdOrdersCount = $this->getOrderService()->searchOrderCount(array(
                    'targetType' => 'course',
                    'targetId' => $course['id'],
                    'status' => 'created',
                    'createdTimeGreaterThan' => strtotime("-30 minutes")
                ));
                $remainingStudentNum = $course['maxStudentNum'] - $course['studentNum'] - $createdOrdersCount;
            }
        }

        return $remainingStudentNum;
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
    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}